<?php

// # Cancelling a SEPA-transaction

// To cancel a transaction, a cancel request with the parent transaction is sent. Voiding  SEPA-transactions
// is only possible before they are forwarded to the bank for settlement.

// ## Required objects

require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// #### Basic configuration
// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// #### SEPA configuration
// Create and add a configuration object with the settings for SEPA direct debit.
$sepaMId = '4c901196-eff7-411e-82a3-5ef6b6860d64';
$sepaKey = 'ecdf5990-0372-47cd-a55d-037dccfe9d25';
$sepaDdConfig = new Config\PaymentMethodConfig(SepaTransaction::DIRECT_DEBIT, $sepaMId, $sepaKey);
$config->add($sepaDdConfig);

// The configration for sepa needs to be also added with the corresponding transaction for credit transfer.
$sepaCtConfig = new Config\PaymentMethodConfig(SepaTransaction::CREDIT_TRANSFER, $sepaMId, $sepaKey);
$config->add($sepaCtConfig);


// The _TransactionService_ is used to execute the cancel operation.
$transactionService = new TransactionService($config);

// ## Cancelling the transaction

$tx = new SepaTransaction();
$tx->setParentTransactionId($_POST['parentTransactionId']);
$response = $transactionService->cancel($tx);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo sprintf('Successfully cancelled.<br> Transaction ID: %s<br>', $response->getTransactionId());

    $txDetailsLink = sprintf(
        'https://api-test.wirecard.com/engine/rest/merchants/%s/payments/%s',
        $sepaMId,
        $response->getTransactionId()
    );
    ?>

    <a href="<?= $txDetailsLink ?>">View transaction details</a>

    <?php
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    // In our example we iterate over all errors and echo them out.
    // You should display them as error, warning or information based on the given severity.
    foreach ($response->getStatusCollection() as $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Entity\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occured.<br>', $severity, $code, $description);
    }
}
