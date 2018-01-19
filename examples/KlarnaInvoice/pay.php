<?php
// # Klarna Invoice amount payment

// The method `pay` of the _transactionService_ provides the means
// to execute a payment with an amount (also known as debit).

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';
//Header design
require __DIR__ . '/../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Transaction\KlarnaInvoiceTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create a `KlarnaInvoiceTransaction` object, which contains all relevant data for the payment process.
$transaction = new KlarnaInvoiceTransaction();
// Use the amount object as amount which has to be paid by the consumer.
$amount = new Amount(2400, 'EUR');
if (!empty($_POST['amount'])) {
	$amount = new Amount((float)$_POST['amount'], 'EUR');
}

// ## Transaction
// The Klarna Guaranteed Invoice transaction holds all transaction relevant data for the reserve process.
$transaction = new KlarnaInvoiceTransaction();
$transaction->setAmount($amount);
$transaction->setLocale('de');

if (array_key_exists('parentTransactionId', $_POST)) {
    $transaction->setParentTransactionId($_POST['parentTransactionId']);
}

$redirect = new Redirect(getUrl('return.php?status=success'), getUrl('return.php?status=cancel'));
$transaction->setRedirect($redirect);

// ### Transaction Service

// The service is used to execute the pay (capture-authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// Since a redirect for successful transactions is defined, a FormInteractionResponse is returned
// if the transaction was successful.
if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
	echo 'Payment successfully completed.<br>';
	echo getTransactionLink($baseUrl, $response);
} elseif ($response instanceof FormInteractionResponse) {
    ?>
    <form method="<?= $response->getMethod(); ?>" action="<?= $response->getUrl(); ?>">
        <?php foreach ($response->getFormFields() as $key => $value): ?>
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
//Footer design
require __DIR__ . '/../inc/footer.php';
