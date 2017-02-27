<?php

// # Cancelling a transaction

// To cancel a transaction, a cancel request with the parent transaction is sent.

// ## Required objects

require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;

// An object containing the data regarding the options of the interface is needed.
$config = new Config('https://api-test.wirecard.com/engine/rest/payments/', '70000-APILUHN-CARD', '8mhwavKVb91T',
// For 3-D Secure transactions a different merchant account id is required than the previously executed seamlessRenderForm
    '33f6d473-3036-4ca5-acb5-8c64dac862d1', '9e0130f6-2e1e-4185-b0d5-dc69079c75cc');

// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);
$tx = new CreditCardTransaction();
$tx->setParentTransactionId($_POST['parentTransactionId']);
$response = $transactionService->cancel($tx);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if($response instanceof SuccessResponse) {
    echo sprintf('Payment successfully cancelled.<br> Transaction ID: %s<br>', $response->getTransactionId());
    ?>

    <?php
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    // In our example we iterate over all errors and echo them out. You should display them as error, warning or information based on the given severity.
    foreach ($response->getStatusCollection() AS $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Entity\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occured.<br>', $severity, $code, $description);
    }
}
