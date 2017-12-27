<?php
// # Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\WeChatTransaction;

// ## Connection

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = 'engine.wechatqrpay';
$httpPass = 'IirVRtCIp9WvtqZp';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.
// The configuration object for payment methods requires three parameters:
// * the name of the payment method
// * the merchant account ID
// * the corresponding secret key

// ### WeChat QRPay

$wechatMAID = 'e9892fc3-1886-4564-8153-f3c7d4dc2b39';
$wechatSecret = '980624d8-3be5-40c8-afca-5347e087a338';
$wechatConfig = new PaymentMethodConfig(WeChatTransaction::NAME, $wechatMAID, $wechatSecret);
$config->add($wechatConfig);
