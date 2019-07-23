<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Redirect with synchronous payments

// At synchronous payment processes the first response already contains the information, that the funds were
// successfully reserved or transferred and the person paying does not need to be redirected to a page from
// a financial service provider. For integration it is sometimes useful, if the responses are similar. Therefore
// the SDK will create a `FormRedirectResponse` for synchronous payment methods, if a redirect URL is provided.

// ### Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../common.php';
require __DIR__ . '/../../configuration/config.php';
require __DIR__ . '/../header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction

// The redirect functionality is illustrated using a credit card transaction, which is synchronous
// for non 3-D Secure transactions.
$transaction = new CreditCardTransaction();
$transaction->setTokenId('5168216323601006');
$transaction->setAmount(new Amount(12.59, 'EUR'));

// ### Redirects

// The redirect URLs are defined in a corresponding object.
// A URL for successful transactions is expected.
$redirectUrl = new Redirect(getUrl('../../payments/CreditCard/return.php?status=success'));

// Set the redirect URL to enable the functionality.
$transaction->setRedirect(($redirectUrl));

// ### Transaction Service

// The service is used to execute the reservation (authorization) operation itself.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);

// ### Response handling

// The response from the service can be used for disambiguation.
// Since the redirect URL is set, a `FormInteractionResponse` will be returned, if the request was successful.
if ($response instanceof FormInteractionResponse) {
    ?>
    <form method="<?= $response->getMethod(); ?>" action="<?= $response->getUrl(); ?>">
        <?php
        // The form fields contain the successful response, therefore these data need to be forwarded.
        foreach ($response->getFormFields() as $key => $value) : ?>
            <input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
        <?php endforeach;
        // Usually an automated transmission of the form would be made.
        // For a better demonstration and for the ease of use this automated submit
        // is replaced with a submit button.
        ?>
        <button type="submit" class="btn btn-primary">Redirect to the success URL</button>
    </form>
    <?php
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
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

require __DIR__ . '/../footer.php';
