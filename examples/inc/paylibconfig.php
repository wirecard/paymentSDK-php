<?php
// # Temporary Configuration Example for paylib

// Basic configuration settings for payment method paylib
// WARNING: This payment method is still in development, please do not use it in its current state

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PaylibTransaction;

// ## Connection

// The basic configuration requires the base URL (Server Address) for Wirecard and the username and password for the HTTP requests.
// WARNING: These are example values for configuration and will not work with paylib
$baseUrl = 'https://test-paylib.free.beeceptor.com';
$httpUser = 'HTTP-USER';
$httpPass = 'HTTP-PASSWORD';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// ### paylib config creation

// paylib payment method can be configured with an individual merchant account ID and the corresponding key.
// For new Config you need the merchant account id (MAID) and secret key to add the payment specific configuration.

$paylibMAID = 'f5f399c1-78b5-4559-bc0c-e077cb686ca9';
$paylibKey = 'NO-SECRET-PROVIDED';
$paylibConfig = new PaymentMethodConfig(PaylibTransaction::NAME, $paylibMAID, $paylibKey);
$config->add($paylibConfig);
