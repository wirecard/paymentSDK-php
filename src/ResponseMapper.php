<?php
namespace Wirecard\PaymentSdk;

class ResponseMapper
{
    public function map($jsonResponse)
    {
        $response = json_decode($jsonResponse, true);
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
        if ($state === 'success') {
            //using isset, because array_key_exists only goes 1 layer deep
            if (isset($payment['payment-methods']['payment-method'][0]['url'])) {
                $redirectUrl = $payment['payment-methods']['payment-method'][0]['url'];
            } else {
                throw new MalformedResponseException('Missing url for redirect in response.');
            }

            if (array_key_exists('transaction-id', $payment)) {
                $transactionId = $payment['transaction-id'];
            } else {
                throw new MalformedResponseException('Missing transaction-id in response.');
            }

            $responseObject = new InteractionResponse($jsonResponse, $statusCollection, $transactionId, $redirectUrl);
        } else {
            $responseObject = new FailureResponse($jsonResponse, $statusCollection);
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
            foreach ($payment['statuses'] as $status) {
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
                $status = new Status($code, $description, $severity);
                $collection->add($status);
            }
        }

        return $collection;
    }
}
