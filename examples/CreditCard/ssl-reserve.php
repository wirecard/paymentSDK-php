<?php

// # Credit card reservation
// The method `reserve` of the _transactionService_ provides the means
// to reserve an amount (also known as authorization).

// ## Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// Create a money object as amount which has to be payed by the consumer.
$amount = new Money(12.59, 'EUR');

// If there was a previous transaction, use the ID of this parent transaction as reference.
$parentTransactionId = array_key_exists('parentTransactionId', $_POST) ? $_POST['parentTransactionId'] : null;

// Otherwise if a token was defined when submitting the credit card data to Wirecard via the UI, this token is used.
$tokenId = array_key_exists('tokenId', $_POST) ? $_POST['tokenId'] : null;

// To make this example usable, even is no transaction or token ID is provided, a predefined existing token ID is set.
if ($parentTransactionId === null && $tokenId === null) {
    $tokenId = '5168216323601006';
}

// ### Config

// Since payment method may have a different merchant ID, a config collection is created.
$configCollection = new Config\PaymentMethodConfigCollection();

// Create and add a configuration object with the settings for credit card
$ccardMId = '9105bb4f-ae68-4768-9c3b-3eda968f57ea';
$ccardKey = 'd1efed51-4cb9-46a5-ba7b-0fdc87a66544';
$ccardConfig = new Config\PaymentMethodConfig(CreditCardTransaction::class, $ccardMId, $ccardKey);
$configCollection->add($ccardConfig);

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APILUHN-CARD';
$httpPass = '8mhwavKVb91T';

// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, $configCollection, 'EUR');


// ## Transaction

// Create a `CreditCardTransaction` object, which contains all relevant data for the payment process.
// The token is required as reference to the credit card data.
$transaction = new CreditCardTransaction();
$transaction->setTokenId($tokenId);
$transaction->setAmount($amount);
$transaction->setParentTransactionId($parentTransactionId);
// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo sprintf('Payment with id %s successfully completed.<br>', $response->getTransactionId());
    $txDetailsLink = sprintf(
        'https://api-test.wirecard.com/engine/rest/merchants/%s/payments/%s',
        $ccardMId,
        $response->getTransactionId()
    );
    ?>

    <a href="<?= $txDetailsLink ?>">View transaction details</a>

    <br><br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="hidden" name="transaction-type" value="ssl"/>
        <input type="submit" value="cancel the payment">
    </form>
    <form action="pay-based-on-reserve.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="hidden" name="transaction-type" value="ssl"/>
        <input type="submit" value="capture the payment">
    </form>

    <?php
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    // In our example we iterate over all errors and display them in a raw state.
    // You should handle them based on the given severity as error, warning or information.
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
