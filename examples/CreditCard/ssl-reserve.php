<?php

// # Credit card amount reservation
// The method `reserve` of the _transactionService_ provides the means to reserve an amount (also known as authorization).

// ## Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Entity\PaymentMethod\CreditCard;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\ReserveTransaction;
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

// An object containing the data regarding the options of the interface is needed.
$config = new Config('https://api-test.wirecard.com/engine/rest/payments/', '70000-APILUHN-CARD', '8mhwavKVb91T',
    '9105bb4f-ae68-4768-9c3b-3eda968f57ea', 'd1efed51-4cb9-46a5-ba7b-0fdc87a66544');

// ## Transaction

// Create a `CreditCardTransaction` object, which contains all relevant data for the payment process.
// The token is required as reference to the credit card data.
$cardData = new CreditCard();
$cardData->setTokenId($tokenId);

$transaction = new ReserveTransaction();
$transaction->setAmount($amount);
$transaction->setPaymentTypeSpecificData($cardData);
$transaction->setParentTransactionId($parentTransactionId);
// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->process($transaction);


// ## Response handling

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
    // In our example we iterate over all errors and display them in a raw state.
    // You should handle them based on the given severity as error, warning or information.
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
