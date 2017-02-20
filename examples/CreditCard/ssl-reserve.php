<?php

// # credit card amount reservation transaction (authorization)
// This example displays the usage of for payment method credit card.

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\CreditCardTransaction;
use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\Money;
use Wirecard\PaymentSdk\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// ### Money object
// Use the money object as amount which has to be payed by the consumer.
$amount = new Money(12.59, 'EUR');

// ### TokenId
// tokens from seamlessRenderForm success callback can be used to execute reservations
$tokenId = array_key_exists('tokenId', $_POST) ? $_POST['tokenId'] : '5168216323601006';

// ### Transaction
// The credit card transaction holds all transaction relevant data for the payment process.
$transaction = new CreditCardTransaction($amount);
$transaction->setTokenId($tokenId);

// ### Config
// The config object holds all interface configuration options
$config = new Config('https://api-test.wirecard.com/engine/rest/payments/', '70000-APILUHN-CARD', '8mhwavKVb91T',
    '9105bb4f-ae68-4768-9c3b-3eda968f57ea', 'd1efed51-4cb9-46a5-ba7b-0fdc87a66544');

// ### Transaction Service
// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);

// ### Response handling
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
