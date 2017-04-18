<?php
// # EPS payment

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\EpsTransaction;
use Wirecard\PaymentSdk\TransactionService;

$amount = new Amount(18.4, 'EUR');

$tx = new EpsTransaction();
$tx->setAmount($amount);

$redirect = new Redirect(
    getUrl('return.php?status=success'),
    getUrl('return.php?status=cancel'),
    getUrl('return.php?status=failure')
);
$tx->setRedirect($redirect);

$transactionService = new TransactionService($config);
$response = $transactionService->pay($tx);

echo get_class($response);

// ## Response handling

// The response from the service can be used for disambiguation.
// Since a redirect for successful transactions is defined, a FormInteractionResponse is returned
// if the transaction was successful.
if ($response instanceof InteractionResponse) {
    header('location: ' . $response->getRedirectUrl());
    exit;
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    echo "The transaction has failed.<br>";

    // In our example we iterate over all errors and display them in a raw state.
    // You should handle them based on the given severity as error, warning or information.
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