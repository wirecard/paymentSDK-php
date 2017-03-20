<?php
// # PayPal return after transaction
// The consumer gets redirected to this page after a PayPal transaction.

// ## Required objects
// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';


use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// #### Basic configuration
// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// #### PayPal
// Create and add a configuration object with the PayPal settings
$paypalMAID = '9abf05c1-c266-46ae-8eac-7f87ca97af28';
$paypalKey = '5fca2a83-89ca-4f9e-8cf7-4ca74a02773f';
$paypalConfig = new Config\PaymentMethodConfig(PayPalTransaction::NAME, $paypalMAID, $paypalKey);
$config->add($paypalConfig);


// ## Transaction

// ### Transaction Service
// The `TransactionService` is used to determine the response from the service provider.
$service = new TransactionService($config);
$response = $service->handleResponse($_POST);


// ## Payment results

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    $xmlResponse = new SimpleXMLElement($response->getRawData());
    $transactionType = $response->getTransactionType();
    if ($transactionType === 'authorization') {
        echo "Reservation";
    } else {
        echo "Payment";
    }
    echo ' successfully completed.<br>';
    $txDetailsLink = sprintf(
        'https://api-test.wirecard.com/engine/rest/merchants/%s/payments/%s',
        $paypalMAID,
        $response->getTransactionId()
    );
    ?>
    Transaction ID: <a href="<?= $txDetailsLink ?>"><?= $response->getTransactionId() ?></a>
    <br>
    <?php
    if ($response->getTransactionType() !== 'authorization-only') {
        ?>
        <form action="cancel.php" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <input type="submit" value="cancel">
        </form>
        <?php
    }
    if ($transactionType === 'authorization') { ?>
        <form action="pay-based-on-reserve.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="hidden" name="transaction-type" value="<?= $transactionType ?>"/>
        <input type="submit" value="capture the payment">
    </form>
    <?php
    }
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
