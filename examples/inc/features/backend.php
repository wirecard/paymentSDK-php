<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Backend Service

// This example displays the usage of backend service. The service can be used to get all possible follow up operations on an transaction, check if the transaction is an "final" transaction, where no follow up operations are possible, or get the correct order state depending on the transaction type. The backend service also provides a process method where a fallback for refund is build in, if an cancel operation fails the SDK will automatically try to make an refund operation if the payment method supports it.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../common.php';
require __DIR__ . '/../../configuration/globalconfig.php';
require __DIR__ . '/../header.php';

use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\Transaction;

// ### Transaction

// To get the possible backend operation you need a transaction.
$transaction = new IdealTransaction();

// Parent transaction needs to be set.
$transaction->setParentTransactionId('02e0a411-44ce-4a41-bd90-062eb586d164');

$backendService = new BackendService($config);

// ### Backend operations

// Get all possible backend operations on the transaction, it is possible to set the limit parameter to true for plugin operations only or leave it default to get all possible operations.
echo "Possible backend operations: ". print_r($backendService->retrieveBackendOperations($transaction), true)
    . "<br/>";

// ### Final status

// Check if the transaction type is final, if so true is returned.
echo "For transaction type debit <br/>";
echo "Is the transaction final: " . printf($backendService->isFinal(Transaction::TYPE_DEBIT)) . "<br/>";

// ### Order state

// We can also get the correct order state depending on the transaction type from the service authorized, cancelled, processing or refunded are possible.
echo "Order state: " . $backendService->getOrderState(Transaction::TYPE_DEBIT);

// #### Backend process

// It is also possible to process orders over the backend service where the fallback for refund is build in. If an cancel transaction fails the sdk will try to do a refund

try {
    // This is only a example of the use, for testing you need to provide a real transaction otherwise it will fail
    $backendService->process($transaction, \Wirecard\PaymentSdk\Transaction\Operation::CANCEL);
} catch (Exception $exception) {
    echo $exception->getMessage();
}

require __DIR__ . '/../footer.php';
