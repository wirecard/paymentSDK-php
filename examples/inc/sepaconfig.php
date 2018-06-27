<?php
// # Maestro Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;

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

// ### SEPA Direct Debit

$sepaDirectDebitMAID = '933ad170-88f0-4c3d-a862-cff315ecfbc0';
$sepaDirectDebitKey = 'ecdf5990-0372-47cd-a55d-037dccfe9d25';
// SEPA requires the creditor ID, therefore a different config object is used.
$sepaDirectDebitConfig = new SepaConfig(SepaDirectDebitTransaction::NAME, $sepaDirectDebitMAID, $sepaDirectDebitKey);
$sepaDirectDebitConfig->setCreditorId('DE98ZZZ09999999999');
$config->add($sepaDirectDebitConfig);

