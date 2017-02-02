<?php
// # PayPal notification
// Wirecard sends a server-to-server request regarding any changes in the transaction status.

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\SuccessResponse;
use Wirecard\PaymentSdk\FailureResponse;
use Monolog\Logger;
use \Monolog\Handler\StreamHandler;

// ### Config
// The `Config` object holds all interface configuration options.
$config = new Config('https://api-test.wirecard.com/engine/rest/paymentmethods/', '70000-APITEST-AP', 'qD2wzQ_hrc!8', '9abf05c1-c266-46ae-8eac-7f87ca97af28', '5fca2a83-89ca-4f9e-8cf7-4ca74a02773f');

// ### Transaction Service
// The `TransactionService` is used to determine the response from the service provider.
$service = new TransactionService($config);

// We use Monolog as logger. Set up a logger for the notifications.
$log = new Logger('Wirecard notifications');
$log->pushHandler(new StreamHandler(__DIR__ . '/../../logs/notify.log', Logger::INFO));

// ### Notification status
// The notification are transmitted as _POST_ request and is handled via the `handleNotification` method.
$notification = $service->handleNotification(file_get_contents('php://input'));

// Log the notification for a successful transaction.
if ($notification instanceof SuccessResponse) {
    $log->info(sprintf('Transaction with id %s was successful.', $notification->getTransactionId()));
// Log the notification for a failed transaction.
} elseif ($notification instanceof FailureResponse) {
    $log->warning(sprintf('Transaction with id %s failed.', $notification->getTransactionId()));
}
