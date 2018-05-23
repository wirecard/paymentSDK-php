<?php
// # Backend Service

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

// ### Transaction

// Creating an example transaction
$transaction = new IdealTransaction();

// To get the backend operations we need to set the parent transaction id
$transaction->setParentTransactionId('02e0a411-44ce-4a41-bd90-062eb586d164');


// ### Example calls
$backendService = new BackendService($config);

// #### Backend operations

// Get all possible backend operations on the transaction, it is possible to set second parameter to true for plugin operations only or leave it default to get all possible operations.
echo "Possible backend operations: ". print_r($backendService->retrieveBackendOperations($transaction), true)
    . "<br/>";

// #### Final status

// Check if the transaction type is final, we get true for final.
echo "For transaction type debit <br/>";
echo "Is the transaction final: " . printf($backendService->isFinal(Transaction::TYPE_DEBIT)) . "<br/>";

// #### Order state

// Depending on the transaction type we get the order state witch can be authorized, cancelled, processing or refunded.
echo "Order state: " . $backendService->getOrderState(Transaction::TYPE_DEBIT);

// #### Backend process

// It is also possible to process orders over the backend service where the fallback for refund is build in. If an cancel transaction fails the sdk will try to do a refund

$backendService->process($transaction, \Wirecard\PaymentSdk\Transaction\Operation::CANCEL);
