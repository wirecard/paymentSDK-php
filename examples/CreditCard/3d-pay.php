<?php
// # 3-D Secure purchase for credit card

// To reserve and capture an amount for a credit card with 3-D secure, you need to use a different transaction object
// in contrast to a transaction with SSL only.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create a amount object as amount which has to be payed by the consumer.
$amount = new Amount(12.59, 'EUR');

// If there was a previous transaction, use the ID of this parent transaction as reference.
$parentTransactionId = array_key_exists('parentTransactionId', $_POST) ? $_POST['parentTransactionId'] : null;

// Otherwise if a token was defined when submitting the credit card data to Wirecard via the UI, this token is used.
$tokenId = array_key_exists('tokenId', $_POST) ? $_POST['tokenId'] : null;

// To make this example usable, even is no transaction or token ID is provided, a predefined existing token ID is set.
if ($parentTransactionId === null && $tokenId === null) {
    $tokenId = '5168216323601006';
}

// The redirect URL determines where the consumer should be redirected to
// after an approval/cancellation on the issuer's ACS page.
$redirectUrl = getUrl('return.php?status=success');


// ## Transaction

// The 3-D credit card transaction contains all relevant data for the payment process.
$transaction = new ThreeDCreditCardTransaction();
$transaction->setAmount($amount);
$transaction->setTokenId($tokenId);
$transaction->setTermUrl($redirectUrl);
$transaction->setParentTransactionId($parentTransactionId);

// ### Transaction Service

// The service is used to execute the payment (authorization + capture) operation itself.
// A response object is returned.
$transactionService = new TransactionService($cardConfig);
$response = $transactionService->pay($transaction);


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
        // Usually an automated transmission of the form would be made.
        // For a better demonstration and for the ease of use this automated submit
        // is replaced with a submit button.
        ?>
        <input type="submit" value="Redirect to 3-D Secure page"></form>
    <?php
elseif ($response instanceof SuccessResponse):
    echo 'Payment successfully completed.<br>';
    echo getTransactionLink($baseUrl, $response);
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="hidden" name="transaction-type" value="3d"/>
        <input type="submit" value="Cancel the payment">
    </form>

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
