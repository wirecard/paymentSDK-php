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
     * @param $jsonResponse
     * @return FailureResponse|InteractionResponse|SuccessResponse
     * @throws MalformedResponseException
     */
    public function map($jsonResponse)
    {
        $response = json_decode($jsonResponse, true);
        if (!is_array($response)) {
            throw new MalformedResponseException('Response is not a valid json string.');
        }
        if (array_key_exists('payment', $response)) {
            $payment = $response['payment'];
        } else {
            throw new MalformedResponseException('Missing payment in response.');
        }

        if (array_key_exists('transaction-state', $payment)) {
            $state = $payment['transaction-state'];
        } else {
            throw new MalformedResponseException('Missing transaction state in response.');
        }


        $statusCollection = $this->getStatusCollection($payment);
        if ($state !== 'success') {
            return new FailureResponse($jsonResponse, $statusCollection);
        }

        if (array_key_exists('transaction-id', $payment)) {
            $transactionId = $payment['transaction-id'];
        } else {
            throw new MalformedResponseException('Missing transaction-id in response.');
        }

        if (!array_key_exists('payment-methods', $payment)) {
            throw new MalformedResponseException('Missing payment method in response.');
        }

        if (!array_key_exists('payment-method', $payment['payment-methods'])) {
            throw new MalformedResponseException('Missing payment method in response.');
        }

        //using isset, because array_key_exists only goes 1 layer deep
        if (isset($payment['payment-methods']['payment-method'][0]['url'])) {
            $redirectUrl = $payment['payment-methods']['payment-method'][0]['url'];
            $responseObject = new InteractionResponse(
                $jsonResponse,
                $statusCollection,
                $transactionId,
                $redirectUrl
            );
        } else {
            $providerTransactionId = $this->retrieveProviderTransactionId($payment);
            $responseObject = new SuccessResponse(
                $jsonResponse,
                $statusCollection,
                $transactionId,
                $providerTransactionId
            );
        }

        return $responseObject;
    }

    /**
     * get the collection of status returned by elastic engine
     * @param $payment
     * @return StatusCollection
     */
    private function getStatusCollection($payment)
    {
        $collection = new StatusCollection();

        if (array_key_exists('statuses', $payment)) {
            foreach ($payment['statuses'] as $statusWrapped) {
                $status = $statusWrapped['status'];
                if (array_key_exists('code', $status)) {
                    $code = $status['code'];
                } else {
                    throw new MalformedResponseException('Missing status code in response.');
                }
                if (array_key_exists('description', $status)) {
                    $description = $status['description'];
                } else {
                    throw new MalformedResponseException('Missing status description in response.');
                }
                if (array_key_exists('severity', $status)) {
                    $severity = $status['severity'];
                } else {
                    throw new MalformedResponseException('Missing status severity in response.');
                }
                $st = new Status($code, $description, $severity);
                $collection->add($st);
            }
        }

        return $collection;
    }

    /**
     * @param $payment
     * @return mixed
     */
    private function retrieveProviderTransactionId($payment)
    {
        $result = null;
        $statuses = $payment['statuses'];
        foreach ($statuses as $st) {
            if (isset($st['status']['provider-transaction-id'])) {
                if ($result !== null) {
                    // Add check
                }
                $result = $st['status']['provider-transaction-id'];
            }
        }

        return $result;
    }
}
