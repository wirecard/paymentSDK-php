<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\BancontactTransaction;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\PaysafecardTransaction;
use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use Wirecard\PaymentSdk\Transaction\UpopTransaction;
use Wirecard\PaymentSdk\Transaction\PayByBankAppTransaction;

// ## Connection

// Get the gateway from environment variables, we use this switching in our CI
$gateway = getenv('GATEWAY');

$gatewayConfig = function ($key) use ($gateway) {
    // if no gateway was defined in the environment, use the api-test.wirecard.com
    if (!$gateway) {
        $gateway = 'API-TEST';
    }
    $dataArray = [
        'NOVA' => [
            'base_url_wppv2' => 'https://payments-test.wirecard.com',
            'base_url' => 'https://payments-test.wirecard.com',
            'http_user' => 'ShopUser',
            'http_pass' => 'pNUPurGGgL',
            'threed_maid' => '9bbd794c-c791-4bc8-9257-35f3f72d2b55',
            'threed_secret' => 'c6e2715e-4d2b-4ba9-b620-8d7b75eed4c1',
            'non_threed_maid' => 'fd83dbfa-8790-4492-8391-3f3938908b28',
            'non_threed_secret' => '38424ae8-2dc5-45be-af4c-6e0fee0fea3e'
        ],
        'API-WDCEE-TEST' => [
            'base_url_wppv2' => 'https://wpp-wdcee-test.wirecard.com',
            'base_url' => 'https://api-wdcee-test.wirecard.com',
            'http_user' => 'pink-test',
            'http_pass' => '8f5y2h0s',
            'threed_maid' => '49ee1355-cdd3-4205-920f-85391bb3865d',
            'threed_secret' => '518c3be1-4aa2-4294-a081-eb7edf20f9d7',
            'non_threed_maid' => '589651ab-bffe-4f45-9a41-c5466aa8cbc8',
            'non_threed_secret'=> 'cf8be86b-a671-4da4-b870-80af5c3eedb1'
        ],
        'API-TEST' => [
            'base_url_wppv2' => 'https://wpp-test.wirecard.com',
            'base_url' => 'https://api-test.wirecard.com',
            'http_user' => '70000-APITEST-AP',
            'http_pass' => 'qD2wzQ_hrc!8',
            'threed_maid' => '508b8896-b37d-4614-845c-26bf8bf2c948',
            'threed_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
            'non_threed_maid' => '53f2895a-e4de-4e82-a813-0d87a10e55e6',
            'non_threed_secret'=> 'dbc5a498-9a66-43b9-bf1d-a618dd399684'
        ],
        'SECURE-TEST-SG' => [
            'base_url_wppv2' => 'https://secure-test.wirecard.com.sg',
            'base_url' => 'https://secure-test.wirecard.com.sg',
            'http_user' => 'uatwd_ecom',
            'http_pass' => 'Tomcat123',
            'threed_maid' => 'd7855010-64c1-4e66-9ab3-d98b309a3d8c',
            'threed_secret' => '543d957b-dcc9-46cd-8258-0f49ed97fa8e',
            'non_threed_maid' => 'd7855010-64c1-4e66-9ab3-d98b309a3d8c',
            'non_threed_secret' => '543d957b-dcc9-46cd-8258-0f49ed97fa8e'
        ],
        'TEST-SG' => [
            'base_url_wppv2' => 'https://test.wirecard.com.sg',
            'base_url' => 'https://test.wirecard.com.sg',
            'http_user' => 'wirecarduser3d',
            'http_pass' => 'Tomcat123',
            'threed_maid' => '961c567b-d9da-41f6-9801-ba21cb228a00',
            'threed_secret' => '03365d5f-1a12-4f16-9351-7ee59ddc9d3f',
            'non_threed_maid' => '961c567b-d9da-41f6-9801-ba21cb228a00',
            'non_threed_secret' => '03365d5f-1a12-4f16-9351-7ee59ddc9d3f'
        ],
        'API-CVC-TEST' => [
            'base_url_wppv2' => 'https://wpp-test.wirecard.com',
            'base_url' => 'https://api-test.wirecard.com',
            'http_user' => '70000-APILUHN-CARD',
            'http_pass' => '8mhwavKVb91T',
            'threed_maid' => 'ba90c606-5d0b-45b9-9902-9b0542bba3a4',
            'threed_secret' => 'b30bf3cc-f365-4929-89e9-d1cbde890f84',
            'non_threed_maid' => 'c1cb3e6c-6ca8-4d82-81b9-861c4246daf4',
            'non_threed_secret' => '9f6b341f-1b6d-4cd4-9a09-505753b485d3'
        ]
    ];

    return $dataArray[$gateway][$key];
};

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = $gatewayConfig('base_url');
$baseUrlWppv2 = $gatewayConfig('base_url_wppv2');
$httpUser = $gatewayConfig('http_user');
$httpPass = $gatewayConfig('http_pass');


// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR', $baseUrlWppv2);


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.
// The configuration object for Credit Card is a little different than other payment methods and can be
// instantiated without any parameters. If you wish to omit non-3-D transactions you can just leave out the
// maid and secret in the default CreditCardConfig. However if you want to use non-3-D transactions you have two
// ways of setting the credentials. First via setting the parameters maid and secret -

// ### Credit Card Non-3-D

$creditcardConfig = new CreditCardConfig();

// - second via using this specific setter.
$creditcardConfig->setNonThreeDCredentials(
    $gatewayConfig('non_threed_maid'),
    $gatewayConfig('non_threed_secret')
);

// Define the limit to allow the maximum amount for a non-3-D transaction, all amounts above this value will be done as
// 3d secure transaction
$creditcardConfig->addNonThreeDMaxLimit(new Amount(100.0, 'EUR'));

// Define the limit to allow the minimum amount for a 3-D transaction, all amounts below or equal the limit will be done
// as non-3-D transaction
$creditcardConfig->addThreeDMinLimit(new Amount(50.0, 'EUR'));

// Amounts larger than threeDMinLimit and smaller or equal nonThreeDLimit will first be tried as 3-D-Secure
// transaction and will fallback on error as non-3D transaction

// ### Credit Card 3-D

$creditcardConfig->setThreeDCredentials(
    $gatewayConfig('threed_maid'),
    $gatewayConfig('threed_secret')
);

$config->add($creditcardConfig);

// ### PayPal
$env = getenv('GATEWAY');

if ('API-WDCEE-TEST' == $env) {
    $paypalMAID = '3191aa81-d930-4e4d-8d95-24bd65b8990b';
    $paypalKey = '995d8de2-0c39-4e98-9216-86f047cb8662';
} else {
    $paypalMAID = '2a0e9351-24ed-4110-9a1b-fd0fee6bec26';
    $paypalKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
}

$paypalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, $paypalMAID, $paypalKey);
$config->add($paypalConfig);

// ### paysafecard

$paysafecardMAID = '28d4938b-d0d6-4c4a-b591-fb63175de53e';
$paysafecardKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$paysafecardConfig = new PaymentMethodConfig(PaysafecardTransaction::NAME, $paysafecardMAID, $paysafecardKey);
$config->add($paysafecardConfig);

// ### Bancontact

$bancontactMAID = 'c41a62ad-aecb-45b3-b367-e0d2cf946ce3';
$bancontactKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$bancontactConfig = new PaymentMethodConfig(BancontactTransaction::NAME, $bancontactMAID, $bancontactKey);
$config->add($bancontactConfig);

// ### Masterpass

$masterpassMAID = '8bc8ed6d-81a8-43be-bd7b-75b008f89fa6';
$masterpassSecret = '2d96596b-9d10-4c98-ac47-4d56e22fd878';
$masterpassConfig = new PaymentMethodConfig(MasterpassTransaction::NAME, $masterpassMAID, $masterpassSecret);
$config->add($masterpassConfig);

// ### UnionPay Online Payments (UPOP)

$upopMAID = 'a908b093-382c-4de9-b26a-624802850216';
$upopSecret = 'b2f8ffd2-7866-44ed-a858-f27f13f0bd77';
$upopConfig = new PaymentMethodConfig(UpopTransaction::NAME, $upopMAID, $upopSecret);
$config->add($upopConfig);

// ### Pay by Bank app

$pbbaMAID = '70055b24-38f1-4500-a3a8-afac4b1e3249';
$pbbaSecret = '	4a4396df-f78c-44b9-b8a0-b72b108ac465';
$pbbaConfig = new PaymentMethodConfig(PayByBankAppTransaction::NAME, $pbbaMAID, $pbbaSecret);
$config->add($pbbaConfig);
