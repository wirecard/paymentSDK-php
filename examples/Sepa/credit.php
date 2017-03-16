<?php
// # SEPA credit transfer

// ## Required objects
// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// #### Basic configuration
// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// The configuration is stored in an object, containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// #### SEPA configuration
// Create and add a configuration object with the settings for SEPA
$sepaMId = '4c901196-eff7-411e-82a3-5ef6b6860d64';
$sepaKey = 'ecdf5990-0372-47cd-a55d-037dccfe9d25';
$sepaConfig = new Config\PaymentMethodConfig(SepaTransaction::NAME, $sepaMId, $sepaKey);
$config->add($sepaConfig);


// ### Transaction related objects
// Create a money object as amount which has to be payed by the consumer.
$amount = null;
if (empty($_POST['amount']) && empty($_POST['parentTransactionId'])) {
    $amount = new Money(10, 'EUR');
}

// The account holder (first name, last name) is required.
$accountHolder = new AccountHolder('Doe');
$accountHolder->setFirstName('Jane');

// A mandate with ID and signed date is required.
$mandate = new Mandate('12345678');


// ## Transaction

// Create a `SepaTransaction` object, which contains all relevant data for the credit process.
$transaction = new SepaTransaction();
if (null !== $amount) {
    $transaction->setAmount($amount);
}

if (array_key_exists('iban', $_POST)) {
    $transaction->setIban($_POST['iban']);

    if (null !== $_POST['bic']) {
        $transaction->setBic($_POST['bic']);
    }
}

if (array_key_exists('parentTransactionId', $_POST)) {
    $transaction->setParentTransactionId($_POST['parentTransactionId']);
}

$transaction->setAccountHolder($accountHolder);
$transaction->setMandate($mandate);

// ### Transaction Service
// The service is used to execute the credit (pending-credit) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->credit($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo sprintf('Credit with id %s successfully completed.<br>', $response->getTransactionId());
    $txDetailsLink = sprintf(
        'https://api-test.wirecard.com/engine/rest/merchants/%s/payments/%s',
        $sepaMId,
        $response->getTransactionId()
    );
    ?>

    <a href="<?= $txDetailsLink ?>">View transaction details</a>

    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="submit" value="cancel the credit">
    </form>
    <form action="credit.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="submit" value="Execute a new credit based on this">
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
