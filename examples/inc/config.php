<?php
// # Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\PaysafecardTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;


// ## Connection
// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// For credit card the HTTP user and password are different, therefore a different configuration is required.
$cardHttpUser = '70000-APILUHN-CARD';
$cardHttpPass = '8mhwavKVb91T';
$cardConfig = new Config\Config($baseUrl, $cardHttpUser, $cardHttpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.

// ### Credit Card SSL

$creditcardMAID = '9105bb4f-ae68-4768-9c3b-3eda968f57ea';
$creditcardKey = 'd1efed51-4cb9-46a5-ba7b-0fdc87a66544';
$creditcardConfig = new PaymentMethodConfig(
    CreditCardTransaction::NAME,
    $creditcardMAID,
    $creditcardKey
);
$cardConfig->add($creditcardConfig);

// ### Credit Card 3-D

$creditcard3dMAID = '33f6d473-3036-4ca5-acb5-8c64dac862d1';
$creditcard3dKey = '9e0130f6-2e1e-4185-b0d5-dc69079c75cc';
$creditcard3dConfig = new PaymentMethodConfig(
    ThreeDCreditCardTransaction::NAME,
    $creditcard3dMAID,
    $creditcard3dKey
);
$cardConfig->add($creditcard3dConfig);

// ### iDEAL

$IdealMAID = 'adb45327-170a-460b-9810-9008e9772f5f';
$IdealSecretKey = '1b9e63b4-c132-42c3-bcbd-2d2e47ae7154';
$IdealConfig = new PaymentMethodConfig(IdealTransaction::NAME, $IdealMAID, $IdealSecretKey);
$config->add($IdealConfig);

// ### PayPal

$paypalMAID = '9abf05c1-c266-46ae-8eac-7f87ca97af28';
$paypalKey = '5fca2a83-89ca-4f9e-8cf7-4ca74a02773f';
$paypalConfig = new Config\PaymentMethodConfig(PayPalTransaction::NAME, $paypalMAID, $paypalKey);
$config->add($paypalConfig);

// ### paysafecard

$paysafecardMAID = '4c0de18e-4c20-40a7-a5d8-5178f0fe95bd';
$paysafecardKey = 'bb1f2975-827b-4aa8-bec6-405191d85fa5';
$paysafecardConfig = new Config\PaymentMethodConfig(PaysafecardTransaction::NAME, $paysafecardMAID, $paysafecardKey);
$config->add($paysafecardConfig);

// #### RatePAY installment

$ratepayMAID = '73ce088c-b195-4977-8ea8-0be32cca9c2e';
$ratepayKey = 'd92724cf-5508-44fd-ad67-695e149212d5';
$ratepayConfig = new Config\PaymentMethodConfig(RatepayInstallmentTransaction::NAME, $ratepayMAID, $ratepayKey);
$config->add($ratepayConfig);

