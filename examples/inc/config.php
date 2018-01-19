<?php
// # Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\BancontactTransaction;
use Wirecard\PaymentSdk\Transaction\EpsTransaction;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\PaysafecardTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayDirectDebitTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use Wirecard\PaymentSdk\Transaction\AlipayCrossborderTransaction;
use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\Transaction\PtwentyfourTransaction;
use Wirecard\PaymentSdk\Transaction\CreditCardMotoTransaction;
use Wirecard\PaymentSdk\Transaction\UpopTransaction;

// ## Connection

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.
// The configuration object for payment methods requires three parameters:
// * the name of the payment method
// * the merchant account ID
// * the corresponding secret key

// ### Credit Card SSL

$creditcardConfig = new CreditCardConfig(
    '53f2895a-e4de-4e82-a813-0d87a10e55e6',
    'dbc5a498-9a66-43b9-bf1d-a618dd399684'
);

// Define the limit to allow the maximum amount for a ssl transaction, all amounts above this value will be done as
// 3d secure transaction
$creditcardConfig->addSslMaxLimit(new Amount(100.0, 'EUR'));

// Define the limit to allow the minimum amount for a 3d transaction, all amounts below or equal the limit will be done
// as ssl transaction
$creditcardConfig->addThreeDMinLimit(new Amount(50.0, 'EUR'));

// Amounts larger than threeDMinLimit and smaller or equal sslMaxLimit will first be tried as 3d secure transaction and
// will fallback on error as ssl transaction

// ### Credit Card 3-D

$creditcardConfig->setThreeDCredentials(
    '508b8896-b37d-4614-845c-26bf8bf2c948',
    'dbc5a498-9a66-43b9-bf1d-a618dd399684'
);

$config->add($creditcardConfig);

// ### Credit Card Moto

$ccardMotoMAID = '53f2895a-e4de-4e82-a813-0d87a10e55e6';
$ccardMotoSecretKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$ccardMotoConfig = new PaymentMethodConfig(CreditCardMotoTransaction::NAME, $ccardMotoMAID, $ccardMotoSecretKey);

$config->add($ccardMotoConfig);

// ### iDEAL

$IdealMAID = 'b4ca14c0-bb9a-434d-8ce3-65fbff2c2267';
$IdealSecretKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$IdealConfig = new PaymentMethodConfig(IdealTransaction::NAME, $IdealMAID, $IdealSecretKey);
$config->add($IdealConfig);

// ### PayPal

$paypalMAID = '2a0e9351-24ed-4110-9a1b-fd0fee6bec26';
$paypalKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$paypalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, $paypalMAID, $paypalKey);
$config->add($paypalConfig);

// ### paysafecard

$paysafecardMAID = '28d4938b-d0d6-4c4a-b591-fb63175de53e';
$paysafecardKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$paysafecardConfig = new PaymentMethodConfig(PaysafecardTransaction::NAME, $paysafecardMAID, $paysafecardKey);
$config->add($paysafecardConfig);

// ### RatePAY

$ratepayMAID = 'fa02d1d4-f518-4e22-b42b-2abab5867a84';
$ratepayKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';

// #### RatePAY Installment

$ratepayInstallmentConfig = new PaymentMethodConfig(RatepayInstallmentTransaction::NAME, $ratepayMAID, $ratepayKey);
$config->add($ratepayInstallmentConfig);

// #### RatePAY Invoice

$ratepayInvoiceConfig = new PaymentMethodConfig(RatepayInvoiceTransaction::NAME, $ratepayMAID, $ratepayKey);
$config->add($ratepayInvoiceConfig);

// ### RatePay Direct Debit

$ratepayDirectDebit = new PaymentMethodConfig(RatepayDirectDebitTransaction::NAME, $ratepayMAID, $ratepayKey);
$config->add($ratepayDirectDebit);

// ### SEPA

$sepaMAID = '4c901196-eff7-411e-82a3-5ef6b6860d64';
$sepaKey = 'ecdf5990-0372-47cd-a55d-037dccfe9d25';
// SEPA requires the creditor ID, therefore a different config object is used.
$sepaConfig = new SepaConfig($sepaMAID, $sepaKey);
$sepaConfig->setCreditorId('DE98ZZZ09999999999');
$config->add($sepaConfig);

// ### Sofortbanking

$sofortMAID = 'c021a23a-49a5-4987-aa39-e8e858d29bad';
$sofortSecretKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$sofortConfig = new PaymentMethodConfig(SofortTransaction::NAME, $sofortMAID, $sofortSecretKey);
$config->add($sofortConfig);

// ### Bancontact

$bancontactMAID = 'c41a62ad-aecb-45b3-b367-e0d2cf946ce3';
$bancontactKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$bancontactConfig = new PaymentMethodConfig(BancontactTransaction::NAME, $bancontactMAID, $bancontactKey);
$config->add($bancontactConfig);

// ### eps

$epsMAID = '20f28c45-e672-470d-bd60-a7bc39720fd2';
$epsSecret = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$epsConfig = new PaymentMethodConfig(EpsTransaction::NAME, $epsMAID, $epsSecret);
$config->add($epsConfig);

// ### Payment on invoice / Payment in advance

$poipiaMAID = '105ab3e8-d16b-4fa0-9f1f-18dd9b390c94';
$poipiaSecret = '2d96596b-9d10-4c98-ac47-4d56e22fd878';
$poipiaConfig = new PaymentMethodConfig(PoiPiaTransaction::NAME, $poipiaMAID, $poipiaSecret);
$config->add($poipiaConfig);

// ### Masterpass

$masterpassMAID = '8bc8ed6d-81a8-43be-bd7b-75b008f89fa6';
$masterpassSecret = '2d96596b-9d10-4c98-ac47-4d56e22fd878';
$masterpassConfig = new PaymentMethodConfig(MasterpassTransaction::NAME, $masterpassMAID, $masterpassSecret);
$config->add($masterpassConfig);

// ### Alipay Crossborder

$alipaycrossborderMAID = '7ca48aa0-ab12-4560-ab4a-af1c477cce43';
$alipaycrossborderSecretKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$alipaycrossborderConfig = new PaymentMethodConfig(AlipayCrossborderTransaction::NAME, $alipaycrossborderMAID, $alipaycrossborderSecretKey);
$config->add($alipaycrossborderConfig);

// ### Przelewy24

$p24Maid = 'afb0aa46-3b0b-4cbf-a91c-5c91ede23701';
$p24Secret = '82fd2e9e-f8e9-42fb-be25-b60a6907c996';
$p24Config = new PaymentMethodConfig(PtwentyfourTransaction::NAME, $p24Maid, $p24Secret);
$config->add($p24Config);

// ### UnionPay Online Payments (UPOP)

$upopMAID = 'a908b093-382c-4de9-b26a-624802850216';
$upopSecret = 'b2f8ffd2-7866-44ed-a858-f27f13f0bd77';
$upopConfig = new PaymentMethodConfig(UpopTransaction::NAME, $upopMAID, $upopSecret);
$config->add($upopConfig);
