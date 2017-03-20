<?php
// # Credit to a credit card
// To transfer funds to a credit card via a credit operation, a token for the corresponding credit card is required.
// A request with the token ID and the account holder name is sent.

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
// #### Basic configuration
// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APILUHN-CARD';
$httpPass = '8mhwavKVb91T';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// #### Configuration for Credit Card SSL
// Create and add a configuration object with the settings for credit card.
$ccardMAID = '33f6d473-3036-4ca5-acb5-8c64dac862d1';
$ccardKey = '9e0130f6-2e1e-4185-b0d5-dc69079c75cc';
$ccardConfig = new Config\PaymentMethodConfig(CreditCardTransaction::NAME, $ccardMAID, $ccardKey);
$config->add($ccardConfig);

// ### Transaction related objects
// Create a money object as amount which has to be payed by the consumer.
$amount = new Money(10.59, 'EUR');

// The account holder last name is required for credit.
$accountHolder = new AccountHolder();
$accountHolder->setLastName('Doe');


// ## Transaction

$transaction = new CreditCardTransaction();
$transaction->setAmount($amount);

// To credit an amount a token ID and the corresponding account holder is required.
$transaction->setTokenId($tokenId);
$transaction->setAccountHolder($accountHolder);

// ### Transaction Service
// The _TransactionService_ is used to generate the request data.
$transactionService = new TransactionService($config);

// The method `credit` is used to transfer funds to the credit card.
$response = $transactionService->credit($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo 'Funds successfully transfered.<br>';
    $txDetailsLink = sprintf(
        'https://api-test.wirecard.com/engine/rest/merchants/%s/payments/%s',
        $ccardMAID,
        $response->getTransactionId()
    );
    ?>
    Transaction ID: <a href="<?= $txDetailsLink ?>"><?= $response->getTransactionId() ?></a>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="hidden" name="transaction-type" value="3d"/>
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
