<?php

// # Handling the response of a transaction

// When a transaction is finished, the response from Wirecard can be read and processed.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;


// ### Config

// Since payment method may have a different merchant ID, a config collection is created.
$configCollection = new Config\PaymentMethodConfigCollection();

// Create and add a configuration object with the settings for credit card.

$ccardMId = '9105bb4f-ae68-4768-9c3b-3eda968f57ea';
$ccardKey = 'd1efed51-4cb9-46a5-ba7b-0fdc87a66544';
$ccardConfig = new Config\PaymentMethodConfig(CreditCardTransaction::class, $ccardMId, $ccardKey);
$configCollection->add($ccardConfig);

// For 3-D Secure transactions a different merchant account ID is required.
$ccard3dMId = '33f6d473-3036-4ca5-acb5-8c64dac862d1';
$ccard3dKey = '9e0130f6-2e1e-4185-b0d5-dc69079c75cc';
$ccard3dConfig = new Config\PaymentMethodConfig(ThreeDCreditCardTransaction::class, $ccard3dMId, $ccard3dKey);
$configCollection->add($ccard3dConfig);

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APILUHN-CARD';
$httpPass = '8mhwavKVb91T';

// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, $configCollection, 'EUR');

// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);

// The 3D-Secure page redirects to the _returnUrl_, which points to this file. To continue the payment process
// the sent data can be fed directly to the transaction service via the method `handleResponse()`.
$response = $transactionService->handleResponse($_POST);

// ## Payment results

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo sprintf('Payment with id %s successfully completed.<br>', $response->getTransactionId());
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="submit" value="cancel the payment">
    </form>
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
