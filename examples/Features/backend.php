<?php
// # backend service

// This example displays the usage of backend service

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';
//Header design
require __DIR__ . '/../inc/header.php';

use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\Transaction;

// Creating an example transaction
$transaction = new IdealTransaction();

// Setting an parent transaction
$transaction->setParentTransactionId('02e0a411-44ce-4a41-bd90-062eb586d164');

$backendService = new BackendService($config);

// Get all possible backend operations on the transaction
echo "Possible backend operations: ". print_r($backendService->retrieveBackendOperations($transaction), true)
    . "<br/>";

echo "For transaction type debit <br/>";
// Check if the transaction type debit is final
echo "Is the transaction final: " . printf($backendService->isFinal(Transaction::TYPE_DEBIT)) . "<br/>";

// Get the order state
echo "Order state: " . $backendService->getOrderState(Transaction::TYPE_DEBIT);
