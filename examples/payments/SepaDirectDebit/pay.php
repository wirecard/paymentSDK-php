<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # SEPA amount payment

// The method `pay` of the _transactionService_ provides the means
// to execute a payment with an amount (also known as debit).

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\TransactionService;

if (!isset($_POST['iban']) && !isset($_POST['parentTransactionId'])) {
    ?>
    <form action="pay.php" method="post">
        <div class="form-group">
            <label for="iban">IBAN:</label>
            <input id="iban" name="iban" value="DE42512308000000060004" class="form-control"/>
        </div>
        <div class="form-group">
            <label for="bic">BIC:</label>
            <input id="bic" name="bic" value="" class="form-control"/>
            <small>e.g. WIREDEMMXXX</small>
        </div>
        <button type="submit" class="btn btn-primary">Pay</button>
    </form>
    <?php
} else {
// ### Transaction related objects

// Create an amount object as amount which has to be paid by the consumer.
    $amount = null;
    if (!empty($_POST['amount'])) {
        $amount = new Amount((float)$_POST['amount'], 'EUR');
    }

    if (empty($_POST['amount']) && empty($_POST['parentTransactionId'])) {
        $amount = new Amount(12.59, 'EUR');
    }

    $accountHolder = new AccountHolder();
// The account holder last name is required.
    $accountHolder->setLastName('Doe');
// The account holders first name is optional.
// For complete list of all fields please visit https://doc.wirecard.com/RestApi_Fields.html
    $accountHolder->setFirstName('Jane');

// A mandate with ID and signed date is required.
    $mandate = new Mandate('12345678');


// ### Transaction

// Create a `SepaDirectDebitTransaction` object, which contains all relevant data for the payment process.
    $transaction = new SepaDirectDebitTransaction();
    if (null !== $amount) {
        $transaction->setAmount($amount);
    }
    if (array_key_exists('iban', $_POST)) {
        $transaction->setIban($_POST['iban']);

        if (null !== $_POST['bic']) {
            $transaction->setBic($_POST['bic']);
        }
    }
    if (array_key_exists('parentTransactionId', $_POST)) {
        $transaction->setParentTransactionId($_POST['parentTransactionId']);
    }
    $transaction->setAccountHolder($accountHolder);
    $transaction->setMandate($mandate);

    $redirect = new Redirect(getUrl('return.php?status=success'), getUrl('return.php?status=cancel'));
    $transaction->setRedirect($redirect);

// ### Transaction Service

// The service is used to execute the pay (pending-debit) operation itself. A response object is returned.
    $transactionService = new TransactionService($config);
    $response = $transactionService->pay($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// Since a redirect for successful transactions is defined, a FormInteractionResponse is returned
// if the transaction was successful.
    if ($response instanceof FormInteractionResponse) {
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
}

require __DIR__ . '/../../inc/footer.php';
