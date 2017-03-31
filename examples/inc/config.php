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
$baseUrl = 'https://api-test.wirecard.com';
$cardHttpUser = '70000-APILUHN-CARD';
$cardHttpPass = '8mhwavKVb91T';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$cardConfig = new Config\Config($baseUrl, $cardHttpUser, $cardHttpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.

// #### Configuration for Credit Card SSL

// Create and add a configuration object with the settings for credit card.
$creditcardMAID = '9105bb4f-ae68-4768-9c3b-3eda968f57ea';
$creditcardKey = 'd1efed51-4cb9-46a5-ba7b-0fdc87a66544';
$creditcardConfig = new Config\PaymentMethodConfig(
    CreditCardTransaction::NAME,
    $creditcardMAID,
    $creditcardKey
);
$cardConfig->add($creditcardConfig);

// ### Credit Card 3-D

// Create and add a configuration object with the settings for credit card.
// For 3-D Secure transactions the corresponding merchant account ID is required.
$creditcard3dMAID = '33f6d473-3036-4ca5-acb5-8c64dac862d1';
$creditcard3dKey = '9e0130f6-2e1e-4185-b0d5-dc69079c75cc';
$creditcard3dConfig = new Config\PaymentMethodConfig(
    ThreeDCreditCardTransaction::NAME,
    $creditcard3dMAID,
    $creditcard3dKey
);
$cardConfig->add($creditcard3dConfig);

