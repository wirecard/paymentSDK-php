<?php
// # eps return after transaction

// The consumer gets redirected to this page after a eps transaction.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';
//Header design
require __DIR__ . '/../../inc/header.php';
require __DIR__ . '/../../inc/payload/eps.php';
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// ## Transaction

// ### Transaction Service

// The `TransactionService` is used to determine the response from the service provider.
$service = new TransactionService($config);
// If there is response data from the service provider handle response
if ($payload) {
    $response = $service->handleResponse($payload);


// ## Payment results

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        echo 'Payment successfully completed.<br>';
        echo getTransactionLink($baseUrl, $response);
// In case of a failed transaction, a `FailureResponse` object is returned.
    } elseif ($response instanceof FailureResponse) {
        // In our example we iterate over all errors and echo them out.
        // You should display them as error, warning or information based on the given severity.
        foreach ($response->getStatusCollection() as $status) {
            /**
             * @var $status \Wirecard\PaymentSdk\Entity\Status
             */
            $severity = ucfirst($status->getSeverity());
            $code = $status->getCode();
            $description = $status->getDescription();
            echo sprintf('%s with code %s and message "%s" occurred.<br>', $severity, $code, $description);
        }
    }
// Otherwise a cancel information is printed
} else {
    echo 'The transaction has been cancelled.<br>';
}
//Footer design
require __DIR__ . '/../../inc/footer.php';
