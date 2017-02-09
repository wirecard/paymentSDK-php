<?php
// # PayPal return after transaction
// The consumer gets redirected to this page after a PayPal transaction.

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSDK\FailureResponse;
use Wirecard\PaymentSdk\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// The `Config` object holds all interface configuration options.
$config = new Config('https://api-test.wirecard.com/engine/rest/paymentmethods/', '70000-APITEST-AP', 'qD2wzQ_hrc!8', '9abf05c1-c266-46ae-8eac-7f87ca97af28', '5fca2a83-89ca-4f9e-8cf7-4ca74a02773f');

// ### Transaction Service
// The `TransactionService` is used to determine the response from the service provider.
$service = new TransactionService($config);

$response = $service->handleResponse($_POST);

// ### Payment results
// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if($response instanceof SuccessResponse) {
    echo sprintf('Payment with id %s successfully completed.<br>', $response->getTransactionId());
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    echo sprintf('Payment with id %s failed.<br>', $response->getTransactionId());
}
