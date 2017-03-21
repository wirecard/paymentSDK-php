<?php
// # Payment after a reservation
// Enter the ID of the successful reserve transaction and start a pay transaction with it.

// ## Required objects
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;

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
$ccardMAID = '9105bb4f-ae68-4768-9c3b-3eda968f57ea';
$ccardKey = 'd1efed51-4cb9-46a5-ba7b-0fdc87a66544';
$ccardConfig = new Config\PaymentMethodConfig(CreditCardTransaction::NAME, $ccardMAID, $ccardKey);
$config->add($ccardConfig);

// #### Configuration for Credit Card 3-D
// For 3-D Secure transactions a different merchant account ID is required.
$ccard3dMAID = '33f6d473-3036-4ca5-acb5-8c64dac862d1';
$ccard3dKey = '9e0130f6-2e1e-4185-b0d5-dc69079c75cc';
$ccard3dConfig = new Config\PaymentMethodConfig(ThreeDCreditCardTransaction::NAME, $ccard3dMAID, $ccard3dKey);
$config->add($ccard3dConfig);


// ## Transaction

if ('3d' === $_POST['transaction-type']) {
    $transaction = new ThreeDCreditCardTransaction();
} else {
    $transaction = new CreditCardTransaction();
}
$transaction->setParentTransactionId($_POST['parentTransactionId']);
if (array_key_exists('amount', $_POST)) {
    $transaction->setAmount(new \Wirecard\PaymentSdk\Entity\Money((float)$_POST['amount'], 'EUR'));
}

// ### Transaction Service
// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo 'Payment successfully completed.<br>';
    echo getTransactionLink($baseUrl, $ccardMAID, $response->getTransactionId());
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="hidden" name="transaction-type" value="<?= $_POST['transaction-type'] ?>"/>
        <input type="submit" value="Cancel the capture">
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
