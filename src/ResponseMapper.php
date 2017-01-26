<?php
/**
 * Created by IntelliJ IDEA.
 * User: juergen.eger
 * Date: 26.01.2017
 * Time: 16:05
 */

namespace Wirecard\PaymentSdk;


class ResponseMapper
{
    public function map($jsonResponse)
    {
        $response = json_decode($jsonResponse, true);
        $payment = array_key_exists('payment', $response) ? $response['payment'] : [];
        $state = array_key_exists('transaction-state', $payment) ? $payment['transaction-state'] : '';

        $statusCollection = $this->getStatusCollection($payment);
        if ($state === 'success') {
            $redirectUrl = isset($payment['payment-methods']['payment-method'][0]['url']) ?
                $payment['payment-methods']['payment-method'][0]['url'] : '';
            $transactionId = isset($payment['transaction-id']) ? $payment['transaction-id'] : '';

            $responseObject = new InteractionResponse($jsonResponse, $statusCollection, $transactionId, $redirectUrl);
        } else {
            $responseObject = new FailureResponse($jsonResponse, $statusCollection);
        }

        return $responseObject;
    }

    private function getStatusCollection($payment)
    {
        $collection = new StatusCollection();

        if (array_key_exists('statuses', $payment)) {
            foreach ($payment['statuses'] AS $status) {
                $code = array_key_exists('code', $status) ?: 0;
                $description = array_key_exists('description', $status) ?: '';
                $severity = array_key_exists('severity', $status) ?: '';
                $status = new Status($code, $description, $severity);
                $collection->add($status);
            }
        }

        return $collection;
    }
}
