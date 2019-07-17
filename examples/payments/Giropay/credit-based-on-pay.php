<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Credit based on pay

// This example shows how SEPA Credit Transfer can be used to refund a giropay payment

// The method `credit` of the _transactionService_ provides the means
// to transfer credits to a specific bank account.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\TransactionService;

if (!isset($_POST['parentTransactionId'])) {
    ?>
    <form action="credit-based-on-pay.php" method="post">
        <div class="form-group">
            <label for="parentTransactionId">Parent transaction id:</label>
            <input id="parentTransactionId" name="parentTransactionId" value="" class="form-control"/>
        </div>
        <button type="submit" class="btn btn-primary">Credit</button>
    </form>
    <?php
} else {
// ### Transaction

// Create a `SepaCreditTransferTransaction` object, which contains all relevant data for the credit process.
    $transaction = new SepaCreditTransferTransaction();

    // Providing the parent transaction id is enough to make a successful "refund". If you wish to transfer
    // a different amount you can set it via `$transaction->setAmount(new Amount(10, 'EUR');`
    if (array_key_exists('parentTransactionId', $_POST)) {
        $transaction->setParentTransactionId($_POST['parentTransactionId']);
    }

// ### Transaction Service

// The service is used to execute the credit (pending-credit) operation itself. A response object is returned.
    $transactionService = new TransactionService($config);
    $response = $transactionService->credit($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        echo 'Refund via SEPA Credit Transfer successfully completed.<br>';
        echo getTransactionLink($baseUrl, $response);
        ?>
        <br>
        <form action="../SepaDirectDebit/cancel.php" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <button type="submit" class="btn btn-primary">Cancel the credit</button>
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
