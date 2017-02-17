<?php
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\FollowupTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// The config object holds all interface configuration options
$config = new Config('https://api-test.wirecard.com/engine/rest/payments/', '70000-APILUHN-CARD', '8mhwavKVb91T',
// For 3-D Secure transactions a different merchant account id is required than the previously executed seamlessRenderForm
    '33f6d473-3036-4ca5-acb5-8c64dac862d1', '9e0130f6-2e1e-4185-b0d5-dc69079c75cc');

// ### Transaction Service
// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$tx = new FollowupTransaction('91b88123-1fd9-489b-bec7-8ac5ccb500d7');
$response = $transactionService->cancel($tx);

var_dump($response);
