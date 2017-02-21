<?php

// # Handling the response of a transaction

// When a transaction is finished, the response from Wirecard can be read and processed.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// An object containing the data regarding the options of the interface is needed.
$config = new Config('https://api-test.wirecard.com/engine/rest/payments/', '70000-APILUHN-CARD', '8mhwavKVb91T',
    '33f6d473-3036-4ca5-acb5-8c64dac862d1', '9e0130f6-2e1e-4185-b0d5-dc69079c75cc');

// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);

// Wirecard sends a POST request to the _returnUrl_, which points to this file. The sent data can be fed
// directly to the transaction service via the method `handleResponse()`.
$response = $transactionService->handleResponse($_POST);

// ## Payment results

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if($response instanceof SuccessResponse) {
    echo sprintf('Payment with id %s successfully completed.<br>', $response->getTransactionId());
?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>" />
        <input type="submit" value="cancel the payment">
    </form>
    <?php
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    // In our example we iterate over all errors and echo them out. You should display them as error, warning or information based on the given severity.
    foreach ($response->getStatusCollection() AS $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occured.<br>', $severity, $code, $description);
    }
}
