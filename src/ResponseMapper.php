<?php
namespace Wirecard\PaymentSdk;

/**
 * Class ResponseMapper
 * @package Wirecard\PaymentSdk
 */
class ResponseMapper
{
    /**
     * map the jsonResponse from engine to ResponseObjects
     *
     * @param $xmlResponse
     * @return FailureResponse|InteractionResponse
     * @throws MalformedResponseException
     */
    public function map($xmlResponse)
    {
        $oldErrorHandling = libxml_use_internal_errors(true);
        $response = simplexml_load_string($xmlResponse);
        libxml_use_internal_errors($oldErrorHandling);
        if (!$response instanceof \SimpleXMLElement) {
            throw new MalformedResponseException('Response is not a valid xml string.');
        }

        if ($response->{'transaction-state'}) {
            $state = (string)$response->{'transaction-state'};
        } else {
            throw new MalformedResponseException('Missing transaction state in response.');
        }

        $statusCollection = $this->getStatusCollection($response);
        if ($state === 'success') {
            //using isset, because array_key_exists only goes 1 layer deep
            if ($response->{'payment-methods'}->{'payment-method'}) {
                $redirectUrl = (string)$response->{'payment-methods'}->{'payment-method'}['url'];
            } else {
                throw new MalformedResponseException('Missing url for redirect in response.');
            }

            if ($response->{'transaction-id'}) {
                $transactionId = (string)$response->{'transaction-id'};
            } else {
                throw new MalformedResponseException('Missing transaction-id in response.');
            }

            $responseObject = new InteractionResponse($xmlResponse, $statusCollection, $transactionId, $redirectUrl);
        } else {
            $responseObject = new FailureResponse($xmlResponse, $statusCollection);
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
