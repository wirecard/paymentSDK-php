<?php
// # Paysafecard notification
// Wirecard sends a server-to-server request regarding any changes in the transaction status.

// ## Required objects
// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PaysafecardTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// #### Basic configuration
// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// #### Paysafecard
// Create and add a configuration object with the Paysafecard settings
$paysafecardMAID = '4c0de18e-4c20-40a7-a5d8-5178f0fe95bd';
$paysafecardKey = 'bb1f2975-827b-4aa8-bec6-405191d85fa5';
$paysafecardConfig = new Config\PaymentMethodConfig(PaysafecardTransaction::NAME, $paysafecardMAID, $paysafecardKey);
$config->add($paysafecardConfig);


// ## Transaction

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
    // In our example we iterate over all errors and echo them out.
    // You should display them as error, warning or information based on the given severity.
    foreach ($notification->getStatusCollection() as $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Entity\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        $log->warning(sprintf('%s with code %s and message "%s" occurred.<br>', $severity, $code, $description));
    }
}
