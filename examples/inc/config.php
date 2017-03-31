<?php

// # Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;


// ## Connection
// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$CardBaseUrl = 'https://api-test.wirecard.com';
$CardHttpUser = '70000-APILUHN-CARD';
$CardHttpPass = '8mhwavKVb91T';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$cardConfig = new Config\Config($CardBaseUrl, $CardHttpUser, $CardHttpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.

// #### Configuration for Credit Card SSL
// Create and add a configuration object with the settings for credit card.
$ccardMAID = '9105bb4f-ae68-4768-9c3b-3eda968f57ea';
$ccardKey = 'd1efed51-4cb9-46a5-ba7b-0fdc87a66544';
$ccardConfig = new Config\PaymentMethodConfig(CreditCardTransaction::NAME, $ccardMAID, $ccardKey);
$config->add($ccardConfig);

// ### Credit Card 3-D
// Create and add a configuration object with the settings for credit card.
// For 3-D Secure transactions the corresponding merchant account ID is required.
$ccard3dMAID = '33f6d473-3036-4ca5-acb5-8c64dac862d1';
$ccard3dKey = '9e0130f6-2e1e-4185-b0d5-dc69079c75cc';
$ccard3dConfig = new Config\PaymentMethodConfig(ThreeDCreditCardTransaction::NAME, $ccard3dMAID, $ccard3dKey);
$config->add($ccard3dConfig);

