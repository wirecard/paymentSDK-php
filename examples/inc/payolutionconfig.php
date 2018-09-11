<?php
// # Payolution Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceTransaction;


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
// The configuration object for payment methods requires three parameters:
// * the name of the payment method
// * the merchant account ID
// * the corresponding secret key

// ### Payolution

$payolutionMAID = '2048677d-57f4-44b0-8d67-9014c6631d5f';
$payolutionSecretKey = '74bd2f0c-6d1b-4e9a-b278-abc34b83ab9f';
$payolutionConfig = new PaymentMethodConfig(PayolutionInvoiceTransaction::NAME, $payolutionMAID, $payolutionSecretKey);
$config->add($payolutionConfig);
