<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # SEPA amount reservation

// The method `reserve` of the _transactionService_ provides the means
// to reserve an amount (also known as authorization).

// ## Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\TransactionService;

if (!isset($_POST['iban'])) {
    ?>
    <form action="reserve.php" method="post">
        <div class="form-group">
            <label for="iban">IBAN:</label>
            <input id="iban" name="iban" value="DE42512308000000060004" class="form-control"/>
        </div>
        <div class="form-group">
            <label for="bic">BIC:</label>
            <input id="bic" name="bic" value="" class="form-control"/>
            <small>e.g. WIREDEMMXXX</small>
        </div>
        <button type="submit" class="btn btn-primary">Reserve</button>
    </form>
    <?php
} else {
// ### Transaction related objects

// Create an amount object as amount which has to be paid by the consumer.
    $amount = new Amount(7, 'EUR');

    $accountHolder = new AccountHolder();
// The account holder last name is required.
    $accountHolder->setLastName('Doe');
// The account holders first name is optional.
// For complete list of all fields please visit https://doc.wirecard.com/RestApi_Fields.html
    $accountHolder->setFirstName('Jane');


// ### Transaction

// Create a `SepaDirectDebitTransaction` object, which contains all relevant data for the payment process.
    $transaction = new SepaDirectDebitTransaction();
    $transaction->setAmount($amount);
    $transaction->setIban($_POST['iban']);
    if (null !== $_POST['bic']) {
        $transaction->setBic($_POST['bic']);
    }
    $transaction->setAccountHolder($accountHolder);

// ### Transaction Service

// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
    $transactionService = new TransactionService($config);
    $response = $transactionService->reserve($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        echo 'Reservation successfully completed.<br>';
        echo getTransactionLink($baseUrl, $response, $config);
        ?>
        <br>
        <form action="pay.php" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <div class="form-group">
                <label for="amount">Amount:</label>
                <input id="amount" name="amount" class="form-control"/>
            </div>
            <button type="submit" class="btn btn-primary">Request a new payment based on this reservation</button>
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
