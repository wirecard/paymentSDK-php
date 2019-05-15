<?php
// # Credit Card WPPv2 Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;

// ## Connection

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://wpp-test.wirecard.com';
$httpUser = '70000-APIDEMO-CARD';
$httpPass = 'ohysS0-dvfMx';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.
// The configuration object for payment methods requires three parameters:
// * the name of the payment method
// * the merchant account ID
// * the corresponding secret key

// ### Credit Card WPPv2

$creditcardwppv2MAID = '7a6dd74f-06ab-4f3f-a864-adc52687270a';
$creditcardwppv2Secret = 'a8c3fce6-8df7-4fd6-a1fd-62fa229c5e55';
$creditcardwppv2Config = new Config\CreditCardConfig($creditcardwppv2MAID, $creditcardwppv2Secret);
$creditcardwppv2Config->setThreeDCredentials($maestroMAID, $maestroSecret);
$config->add($creditcardwppv2Config);
