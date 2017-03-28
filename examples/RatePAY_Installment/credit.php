<?php
// # Credit via RatePAY
// To transfer funds via a credit operation, information on the receiver are required.
// A request is sent with the account holder information.

// ## Required objects
// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
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

// #### Config for RatePAY
// Create and add a configuration object with the RatePAY settings
$ratepayMAID = '9abf05c1-c266-46ae-8eac-7f87ca97af28';
$ratepayKey = '5fca2a83-89ca-4f9e-8cf7-4ca74a02773f';

$ratepayConfig = new Config\PaymentMethodConfig(
    RatepayInstallmentTransaction::NAME,
    $ratepayMAID,
    $ratepayKey
);
$config->add($ratepayConfig);

// Use the money object as amount which has to be payed by the consumer.
if (array_key_exists('amount', $_POST)) {
    $amountValue = $_POST['amount'];
} else {
    $amountValue = 100;
}
$amount = new Money($amountValue, 'EUR');

// ### Redirect URLs
// The redirect URLs determine where the consumer should be redirected by RatePAY after approval/cancellation.
$redirectUrls = new Redirect(getUrl('return.php?status=success'), getUrl('return.php?status=cancel'));

// ### Notification URL
// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

$credit1 = new \Wirecard\PaymentSdk\Entity\Item('Credit 1', $amount, 1);
$credit1->setArticleNumber('C1');
$credit1->setTaxRate(0.0);

$itemCollection = new \Wirecard\PaymentSdk\Entity\ItemCollection();
$itemCollection->add($credit1);


// ## Transaction

// The RatePAY transaction holds all transaction relevant data for the payment process.
$transaction = new RatepayInstallmentTransaction();
$transaction->setNotificationUrl($notificationUrl);
$transaction->setRedirect($redirectUrls);
$transaction->setAmount($amount);
$transaction->setParentTransactionId($_POST['parentTransactionId']);
$transaction->setItemCollection($itemCollection);


// ### Transaction Service
// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->credit($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo 'Funds successfully transferred.<br>';
    echo getTransactionLink($baseUrl, $ratepayMAID, $response->getTransactionId());
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
        echo sprintf('%s with code %s and message "%s" occurred.<br>', $severity, $code, $description);
    }
}
