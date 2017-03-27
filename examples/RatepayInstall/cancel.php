<?php
// # Cancelling a transaction
// To cancel a transaction, a cancel request with the parent transaction is sent.

// ## Required objects
// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\RatepayInstallTransaction;
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

// #### RatePay installment
// Create and add a configuration object with the RatePay installment settings
$ratepayInstallMAID = '73ce088c-b195-4977-8ea8-0be32cca9c2e';
$ratepayInstallKey = 'd92724cf-5508-44fd-ad67-695e149212d5';

$ratepayInstallConfig = new Config\PaymentMethodConfig(
    RatepayInstallTransaction::NAME,
    $ratepayInstallMAID,
    $ratepayInstallKey
);
$config->add($ratepayInstallConfig);


// ### Transaction related objects
// Use the money object as amount which has to be payed by the consumer.
if (array_key_exists('amount', $_POST)) {
    $amountValue = $_POST['amount'];
} else {
    $amountValue = 2400;
}
$amount = new Money($amountValue, 'EUR');

// The order number
$orderNumber = 'A2';


// #### Order items
// Create your items.
$item1 = new \Wirecard\PaymentSdk\Entity\Item('Item 1', new Money(400, 'EUR'), 1);
$item1->setArticleNumber('A1');
$item1->setTaxRate(0.1);

$item2 = new \Wirecard\PaymentSdk\Entity\Item('Item 2', new Money(1000, 'EUR'), 2);
$item2->setArticleNumber('B2');
$item2->setTaxRate(0.2);

// Create an item collection to store the items.
$itemCollection = new \Wirecard\PaymentSdk\Entity\ItemCollection();
$itemCollection->add($item1);
$itemCollection->add($item2);


// ## Transaction

$transaction = new RatepayInstallTransaction();
$transaction->setParentTransactionId($_POST['parentTransactionId']);
$transaction->setAmount($amount);
$transaction->setOrderNumber($orderNumber);
$transaction->setItemCollection($itemCollection);

// ### Transaction Service
// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);
$response = $transactionService->cancel($transaction);

// ## Response handling
// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo 'Payment successfully completed.<br>';
    echo getTransactionLink($baseUrl, $ratepayInstallMAID, $response->getTransactionId());
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
