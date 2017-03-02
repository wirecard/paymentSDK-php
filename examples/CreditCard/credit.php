<?php

// # Cancelling a transaction

// To cancel a transaction, a cancel request with the parent transaction is sent.

// ## Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;

// Define the token for the credit card where the amount should be credited.
$tokenId = '4304509873471003';

// ### Config

// Since payment method may have a different merchant ID, a config collection is created.
$configCollection = new Config\PaymentMethodConfigCollection();

// Create and add a configuration object with the settings for credit card.
// For 3-D Secure transactions a different merchant account ID is required
// than for the previously executed _seamlessRenderForm_.

$ccardMId = '33f6d473-3036-4ca5-acb5-8c64dac862d1';
$ccardKey = '9e0130f6-2e1e-4185-b0d5-dc69079c75cc';
$ccardConfig = new Config\PaymentMethodConfig(CreditCardTransaction::class, $ccardMId, $ccardKey);
$configCollection->add($ccardConfig);

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APILUHN-CARD';
$httpPass = '8mhwavKVb91T';

// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, $configCollection, 'EUR');

// Create a money object as amount which has to be payed by the consumer.
$amount = new Money(10.59, 'EUR');

// The account holder last name is required for credit.
$accountHolder = new AccountHolder('Doe');

// The _TransactionService_ is used to generate the request data.
$transactionService = new TransactionService($config);
$tx = new CreditCardTransaction();
$tx->setAmount($amount);

// To credit an amount a token ID and the corresponding account holder is required.
$tx->setTokenId($tokenId);
$tx->setAccountHolder($accountHolder);

// The method `credit` is used to transfer funds to the credit card.
$response = $transactionService->credit($tx);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo sprintf('Funds successfully transfered.<br> Transaction ID: %s<br>', $response->getTransactionId());
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="submit" value="cancel the credit">
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
