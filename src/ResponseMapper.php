<?php
namespace Wirecard\PaymentSdk;

/**
 * Class ResponseMapper
 * @package Wirecard\PaymentSdk
 */
class ResponseMapper
{
    /**
     * map the xml Response from engine to ResponseObjects
     *
     * @param $xmlResponse
     * @return FailureResponse|InteractionResponse|SuccessResponse
     * @throws MalformedResponseException
     */
    public function map($xmlResponse)
    {
        //we need to use internal_errors, because we don't want to throw errors on invalid xml responses
        $oldErrorHandling = libxml_use_internal_errors(true);
        $response = simplexml_load_string($xmlResponse);
        //reset to old value after string is loaded
        libxml_use_internal_errors($oldErrorHandling);
        if (!$response instanceof \SimpleXMLElement) {
            throw new MalformedResponseException('Response is not a valid xml string.');
        }

        //we have to string cast all fields, otherwise the contain SimpleXMLElements

        if (isset($response->{'transaction-state'})) {
            $state = (string)$response->{'transaction-state'};
        } else {
            throw new MalformedResponseException('Missing transaction state in response.');
        }

        $statusCollection = $this->getStatusCollection($response);
        if ($state !== 'success') {
            return new FailureResponse($xmlResponse, $statusCollection);
        }

        if (!isset($response->{'transaction-id'})) {
            throw new MalformedResponseException('Missing transaction-id in response.');
        }
        $transactionId = (string)$response->{'transaction-id'};

        if (!isset($response->{'payment-methods'})) {
            throw new MalformedResponseException('Missing payment methods in response.');
        }

        if (!isset($response->{'payment-methods'}->{'payment-method'})) {
            throw new MalformedResponseException('Payment methods is empty in response.');
        }

        if (count($response->{'payment-methods'}->{'payment-method'}) > 1) {
            throw new MalformedResponseException('More payment methods in response.');
        }

        if (isset($response->{'payment-methods'}->{'payment-method'}['url'])) {
            $redirectUrl = (string)$response->{'payment-methods'}->{'payment-method'}['url'];
            $responseObject = new InteractionResponse($xmlResponse, $statusCollection, $transactionId, $redirectUrl);
        } else {
            $providerTransactionId = $response->{'statuses'}->{'status'}['provider-transaction-id'];
            $responseObject = new SuccessResponse(
                $xmlResponse,
                $statusCollection,
                $transactionId,
                $providerTransactionId
            );
        }

        return $responseObject;
    }

    /**
     * get the collection of status returned by elastic engine
     * @param \SimpleXMLElement $payment
     * @return StatusCollection
     */
    private function getStatusCollection($payment)
    {
        $collection = new StatusCollection();

        /**
         * @var $statuses \SimpleXMLElement
         */
        $statuses = $payment->statuses;
        if (count($statuses->status) > 0) {
            foreach ($statuses->status as $statusNode) {
                /**
                 * @var $statusNode \SimpleXMLElement
                 */
                $attributes = $statusNode->attributes();

                if ((string)$attributes['code'] !== '') {
                    $code = (string)$attributes['code'];
                } else {
                    throw new MalformedResponseException('Missing status code in response.');
                }
                if ((string)$attributes['description'] !== '') {
                    $description = (string)$attributes['description'];
                } else {
                    throw new MalformedResponseException('Missing status description in response.');
                }
                if ((string)$attributes['severity'] !== '') {
                    $severity = (string)$attributes['severity'];
                } else {
                    throw new MalformedResponseException('Missing status severity in response.');
                }
                $status = new Status($code, $description, $severity);
                $collection->add($status);
            }
        }

        return $collection;
    }
}
