<?php

if (!isset($_POST['paymentToken'])) {
    die('Please pass the token that you received in the <a href="pay-button.php">previous step</a>.');
}

/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Google Pay payment transaction

// This example displays the usage payments for payment method Google Pay.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/config.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\GooglePayTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create a amount object as amount which has to be paid by the consumer.
$amount = new Amount($googlePayTotalPrice, $googlePayCurrencyCode);

// Set redirect URLs for success, cancel and failure.
// From payment page you will be redirected to:
// Success URL when the payment is approved.
// Cancel URL when the user cancels the transaction on payment page.
// Failure URL when payment is not approved or the data are missing or incorrect
$redirectUrls = new Redirect(
    getUrl('return.php?status=success'),
    getUrl('return.php?status=cancel'),
    getUrl('return.php?status=failure')
);

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// ### Transaction

// The Google Pay transaction holds all transaction relevant data for the payment process.
$transaction = new GooglePayTransaction();

// ### Mandatory fields
$transaction->setNotificationUrl($notificationUrl);
$transaction->setRedirect($redirectUrls);
$transaction->setAmount($amount);

// ### Optional fields
// For the full list of fields see: https://doc.wirecard.com/RestApi_Fields.html
$transaction->setIpAddress('127.0.0.1');

// Set cryptogram value
$transaction->setCryptogramValue($_POST['paymentToken']);

// ### Transaction Service
// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// If a redirect of the customer is required a `FormInteractionResponse` object is returned.
if ($response instanceof FormInteractionResponse):
    // A form for redirect should be created and submitted afterwards.
    ?>
    <form method="<?= $response->getMethod(); ?>" action="<?= $response->getUrl(); ?>">
        <?php foreach ($response->getFormFields() as $key => $value) : ?>
            <input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
        <?php endforeach;
        // Usually an automated transmission of the form would be made.
        // For a better demonstration and for the ease of use this automated submit
        // is replaced with a submit button.
        ?>
        <button type="submit" class="btn btn-primary">Redirect to result page</button>
    </form>
<?php
elseif ($response instanceof SuccessResponse) :
    echo 'Payment successfully completed.<br>';
    echo getTransactionLink($baseUrl, $response);
    echo '<br>Credit Card Token-Id: ' . $response->getCardTokenId();
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="hidden" name="amount" value="<?= $response->getRequestedAmount()->getValue() ?>"/>
        <input type="hidden" name="currency" value="<?= $response->getRequestedAmount()->getCurrency() ?>"/>
        <button type="submit" class="btn btn-primary">Cancel the payment</button>
    </form>

<?php
// In case of a failed transaction, a `FailureResponse` object is returned.
elseif ($response instanceof FailureResponse) :
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

require __DIR__ . '/../../inc/footer.php';

