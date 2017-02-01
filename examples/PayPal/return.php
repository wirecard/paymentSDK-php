<?php
// # PayPal return after transaction
// The consumer gets redirected to this page after a paypal transaction

// we are using psr-4 autoloading through composer
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// The config object holds all interface configuration options
$config = new Config('https://api-test.wirecard.com/engine/rest/paymentmethods/', '70000-APITEST-AP', 'qD2wzQ_hrc!8', '9abf05c1-c266-46ae-8eac-7f87ca97af28', '5fca2a83-89ca-4f9e-8cf7-4ca74a02773f');

// ### Transaction Service
// The service is used to determine the response from the service provider.
$service = new TransactionService($config);

$response = $service->handleResponse($_POST);

