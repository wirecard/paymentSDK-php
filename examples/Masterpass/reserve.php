<?php
// # Credit card reservation

// The method `reserve` of the _transactionService_ provides the means
// to reserve an amount (also known as authorization).

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create a amount object as amount which has to be paid by the consumer.
$amount = new Amount(70.00, 'EUR');

// The redirect URL determines where the consumer should be redirected to
// after an approval/cancellation on the issuer's ACS page.
$redirectUrl = new \Wirecard\PaymentSdk\Entity\Redirect(getUrl('return.php?status=success'));


// ## Transaction

// The credit card transaction contains all relevant data for the payment process.
$transaction = new MasterpassTransaction();
$transaction->setAmount($amount);
$transaction->setRedirect($redirectUrl);

// ### Transaction Service

// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// If a redirect of the customer is required a `FormInteractionResponse` object is returned.
if ($response instanceof InteractionResponse):
    header("Location: {$response->getRedirectUrl()}");
// In case of a failed transaction, a `FailureResponse` object is returned.
elseif ($response instanceof FailureResponse):
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
endif;
