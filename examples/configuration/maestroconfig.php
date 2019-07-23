<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Maestro Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;

// ## Connection

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-wdcee-test.wirecard.com';
$httpUser = 'plugin-test';
$httpPass = '4-41N4\lI0]783';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.
// The configuration object for payment methods requires three parameters:
// * the name of the payment method
// * the merchant account ID
// * the corresponding secret key

// ### Maestro

$maestroMAID = '4945f0ef-51e0-43af-972f-885405320842';
$maestroSecret = '822e87ea-dcc3-4d01-861c-e39f14a0ab6c';
$maestroConfig = new Config\MaestroConfig(null, null);
$maestroConfig->setThreeDCredentials($maestroMAID, $maestroSecret);
$config->add($maestroConfig);
