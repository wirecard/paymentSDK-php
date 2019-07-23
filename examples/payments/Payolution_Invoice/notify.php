<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Handling Payolution Invoice notification

// Wirecard sends a server-to-server request regarding any changes in the transaction status.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../configuration/config.php';

require __DIR__ . '/../../inc/header.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// ## Validation

// Set a public key for certificate pinning used for response signature validation, this certificate needs to be always
// up to date
$config->setPublicKey(file_get_contents(__DIR__ . '/../../inc/api-test.wirecard.com.crt'));

// ### Transaction Service

// The `TransactionService` is used to determine the response from the service provider.
$service = new TransactionService($config);

// We use Monolog as logger. Set up a logger for the notifications.
$log = new Logger('Wirecard notifications');
$log->pushHandler(new StreamHandler(__DIR__ . '/../../../logs/notify.log', Logger::INFO));

// ## Notification status

try {
// The notification are transmitted as _POST_ request and is handled via the `handleNotification` method.
    $notification = $service->handleNotification(file_get_contents('php://input'));

// Log the notification for a successful transaction.
    if ($notification instanceof SuccessResponse) {
        $log->info(sprintf(
            'Transaction with id %s was successful and validation status is %s.',
            $notification->getTransactionId(),
            $notification->isValidSignature() ? 'true' : 'false'
        ));
        $log->info(print_r(['notification' => $notification], true));

// Log the notification for a failed transaction.
    } elseif ($notification instanceof FailureResponse) {
        $log->info(sprintf(
            'Transaction with id %s was failure and validation status is %s.',
            $notification->getTransactionId(),
            $notification->isValidSignature() ? 'true' : 'false'
        ));

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

// maybe the result is not a valid XML?
} catch (MalformedResponseException $mre) {
    $log->error(sprintf('Cannot interprete received data as notification: %s', $mre->getMessage()));
}


require __DIR__ . '/../../inc/footer.php';
