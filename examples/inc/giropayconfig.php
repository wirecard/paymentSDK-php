<?php
// # Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\GiroPayTransaction;

// ## Connection

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '16390-testing';
$httpPass = '3!3013=D3fD8X7';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.
// The configuration object for Credit Card is a little different than other payment methods and can be
// instantiated without any parameters. If you wish to omit ssl transactions you can just leave out the
// maid and secret in the default CreditCardConfig. However if you want to use ssl transactions you have two
// ways of setting the credentials. First via setting the parameters maid and secret -

// ### GiroPay
$giropayMAID = '9b4b0e5f-1bc8-422e-be42-d0bad2eadabc';
$giropaySecret = '0c8c6f3a-1534-4fa1-99d9-d1c644d43709';
$giropayConfig = new PaymentMethodConfig(GiroPayTransaction::NAME, $giropayMAID, $giropaySecret);
$config->add($giropayConfig);
