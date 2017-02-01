<?php
// # PayPal return after transaction
// The consumer gets redirected to this page after a PayPal transaction.

// PSR-4 autoloading is used through composer.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\TransactionService;
use Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use Wirecard\PaymentSdk\SuccessResponse;
use Wirecard\PaymentSdk\FailureResponse;

// ### Config
// The `Config` object holds all interface configuration options.
$config = new Config('https://api-test.wirecard.com/engine/rest/paymentmethods/', '70000-APITEST-AP', 'qD2wzQ_hrc!8', '9abf05c1-c266-46ae-8eac-7f87ca97af28', '5fca2a83-89ca-4f9e-8cf7-4ca74a02773f');

// ### Transaction Service
// The `TransactionService` is used to determine the response from the service provider.
$service = new TransactionService($config);

// ### Notification status
$response = $service->handleNotification($_POST);

// We use Monolog as logger. Set up a logger for the notifications.
$log = new Logger('Wirecard notifications');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/notify.log', Logger::INFO));

// Log the notification for a successful transaction.
if ($response instanceof SuccessResponse ) {
    $log->info(sprintf('Transaction with id %s was successful.', $response->getTransactionId()));
// Log the notification for a falied transaction.
} elseif($response instanceof FailureResponse) {
    $log->warning(sprintf('Transaction with id %s failed.', $response->getTransactionId()));
}
