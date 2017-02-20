<?php
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\FollowupTransaction;
use Wirecard\PaymentSdk\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// The config object holds all interface configuration options
$config = new Config('https://api-test.wirecard.com/engine/rest/payments/', '70000-APILUHN-CARD', '8mhwavKVb91T',
// For 3-D Secure transactions a different merchant account id is required than the previously executed seamlessRenderForm
    '33f6d473-3036-4ca5-acb5-8c64dac862d1', '9e0130f6-2e1e-4185-b0d5-dc69079c75cc');

// ### Transaction Service
// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$tx = new FollowupTransaction($_POST['parentTransactionId']);
$response = $transactionService->cancel($tx);

// ### Response handling
// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if($response instanceof SuccessResponse) {
    echo sprintf('Payment successfully cancelled.<br> Transaction ID: %s<br>', $response->getTransactionId());
    ?>

    <?php
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    // In our example we iterate over all errors and echo them out. You should display them as error, warning or information based on the given severity.
    foreach ($response->getStatusCollection() AS $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occured.<br>', $severity, $code, $description);
    }
}
