<?php
// # Reservation for credit card with 3-D secure

// To reserve an amount for a credit card with 3-D secure, you need to use a different transaction object.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create a amount object as amount which has to be paid by the consumer.
$amount = new Amount(12.59, 'EUR');

// Tokens from a successful _seamlessRenderForm_ callback can be used to execute reservations.
// If no token ID is provided, a predefined ID is used.
$tokenId = array_key_exists('tokenId', $_POST) ? $_POST['tokenId'] : '5168216323601006';

// The redirect URL determines where the consumer should be redirected to
// after an approval/cancellation on the issuer's ACS page.
$redirectUrl = getUrl('return.php?status=success');


// ## Transaction

// The 3-D credit card transaction contains all relevant data for the payment process.
$transaction = new ThreeDCreditCardTransaction();
$transaction->setAmount($amount);
$transaction->setTokenId($tokenId);
$transaction->setTermUrl($redirectUrl);

// ### Transaction Service

// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($cardConfig);
$response = $transactionService->reserve($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// If a redirect of the customer is required a `FormInteractionResponse` object is returned.
if ($response instanceof FormInteractionResponse):
    // A form for redirect should be created and submitted afterwards.
    ?>
    <form method="<?= $response->getMethod(); ?>" action="<?= $response->getUrl(); ?>">
        <?php foreach ($response->getFormFields() as $key => $value): ?>
            <input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
        <?php endforeach;
        // For a better demonstration and for the ease of use the automatic submit was replaced with a submit button.
        ?>
        <input type="submit" value="Redirect to 3-D Secure page"></form>
    <?php
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
