<?php
// # Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\PaysafecardTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
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
$creditcardConfig = new PaymentMethodConfig(
    CreditCardTransaction::NAME,
    $creditcardMAID,
    'd1efed51-4cb9-46a5-ba7b-0fdc87a66544'
);
$cardConfig->add($creditcardConfig);

// ### Credit Card 3-D

$creditcard3dConfig = new PaymentMethodConfig(
    ThreeDCreditCardTransaction::NAME,
    '33f6d473-3036-4ca5-acb5-8c64dac862d1',
    '9e0130f6-2e1e-4185-b0d5-dc69079c75cc'
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
$paypalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, $paypalMAID, $paypalKey);
$config->add($paypalConfig);

// ### paysafecard

$paysafecardMAID = '4c0de18e-4c20-40a7-a5d8-5178f0fe95bd';
$paysafecardKey = 'bb1f2975-827b-4aa8-bec6-405191d85fa5';
$paysafecardConfig = new PaymentMethodConfig(PaysafecardTransaction::NAME, $paysafecardMAID, $paysafecardKey);
$config->add($paysafecardConfig);

// ### RatePAY

$ratepayMAID = '73ce088c-b195-4977-8ea8-0be32cca9c2e';
$ratepayKey = 'd92724cf-5508-44fd-ad67-695e149212d5';

// #### RatePAY Installment

$ratepayInstallmentConfig = new PaymentMethodConfig(
    RatepayInstallmentTransaction::NAME,
    $ratepayMAID,
    $ratepayKey
);
$config->add($ratepayInstallmentConfig);

// #### RatePAY Invoice

$ratepayInvoiceConfig = new PaymentMethodConfig(RatepayInvoiceTransaction::NAME, $ratepayMAID, $ratepayKey);
$config->add($ratepayInvoiceConfig);

// ### SEPA

$sepaMAID = '4c901196-eff7-411e-82a3-5ef6b6860d64';
$sepaKey = 'ecdf5990-0372-47cd-a55d-037dccfe9d25';
// SEPA requires the creditor ID, therefore a different config object is used.
$sepaConfig = new SepaConfig(SepaTransaction::NAME, $sepaMAID, $sepaKey);
$sepaConfig->setCreditorId('DE98ZZZ09999999999');
$config->add($sepaConfig);

// ### Sofortbanking

$sofortMAID = 'f19d17a2-01ae-11e2-9085-005056a96a54';
$sofortSecretKey = 'ad39d9d9-2712-4abd-9016-cdeb60dc3c8f';
$sofortConfig = new PaymentMethodConfig(SofortTransaction::NAME, $sofortMAID, $sofortSecretKey);
$config->add($sofortConfig);
