<?php
// # Validating a response from Wirecard

// When a transaction is finished, the response from Wirecard can be validated using XML validation
// and certificate pinning.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// A response from Wirecard includes a signature. This signature can be validated but requires
// a public key for certificate pinning. The provided certificate needs to be **always up-to-date**.
$config->setPublicKey(file_get_contents(__DIR__ . '/../inc/api-test.wirecard.com.crt'));

// ## Transaction

// ### Transaction Service

// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($cardConfig);

// The POST data is processed with the method `handleResponse()`.
$response = $transactionService->handleResponse($_POST);


// ## Payment results

// We want to check the successful responses.
if ($response instanceof SuccessResponse) {
    // The validity of the response can be checked with the method `isValidSignature()`.
    echo sprintf('Response validation status: %s <br>', $response->isValidSignature() ? 'true' : 'false');
} else {
    echo "Transaction was not successful.";
}
