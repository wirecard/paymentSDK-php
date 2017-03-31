<?php
// # SEPA credit transfer

// The method `credit` of the _transactionService_ provides the means
// to transfer credits to a specific bank account.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create an amount object as amount which has to be payed by the consumer.
$amount = null;
if (empty($_POST['amount']) && empty($_POST['parentTransactionId'])) {
    $amount = new Amount(10, 'EUR');
}

// The account holder (first name, last name) is required.
$accountHolder = new AccountHolder();
$accountHolder->setLastName('Doe');
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
    echo 'Credit successfully completed.<br>';
    echo getTransactionLink($baseUrl, $sepaMAID, $response->getTransactionId());
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="submit" value="Cancel the credit">
    </form>
    <form action="credit.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="submit" value="Request a new credit based on this credit">
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
        echo sprintf('%s with code %s and message "%s" occurred.<br>', $severity, $code, $description);
    }
}
