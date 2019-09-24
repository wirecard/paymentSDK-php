<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Pay with payment method paylib
// WARNING: This payment method is still in development, please do not use it in its current state

// ## Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/config.php';
//Header design
require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Transaction\PaylibTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create a amount object as amount which has to be paid by the consumer.
$amount = new Amount(1.0, 'EUR');

$accountHolder = new AccountHolder();
$accountHolder->setFirstName('John');
$accountHolder->setLastName('Doe');
$accountHolder->setEmail('john.doe@email.com');

$redirect = new Redirect(
    getUrl('return.php?status=success'),
    getUrl('return.php?status=cancel'),
    getUrl('return.php?status=failure')
);

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');



// ### Transaction

$transaction = new PaylibTransaction();
$transaction->setAccountHolder($accountHolder);
$transaction->setAmount($amount);
$transaction->setRedirect($redirect);

// ### Transaction Service
// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `InteractionResponse` object is returned including the payload
// information for redirect.
if ($response instanceof FormInteractionResponse) {
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
		<button type="submit" class="btn btn-primary">Redirect to Paylib</button>
    </form>
 <?php
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
//Footer design
require __DIR__ . '/../../inc/footer.php';


/*
 *
        if (strval($paymentMethod['name']) === PaylibTransaction::NAME) {
            $fields = new FormFieldMap();

            foreach($paymentMethod->payload->{'payload-field'} as $payload) {
                $fields->add(strval($payload['field-name']), strval($payload['field-value']));
            }

            $response = new FormInteractionResponse($this->simpleXml, strval($paymentMethod['url']));
            $response->setFormFields($fields);

            return $response;
        }
 */