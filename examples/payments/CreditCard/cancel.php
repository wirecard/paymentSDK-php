<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Cancelling a transaction

// To cancel a transaction, a cancel request with the parent transaction is sent.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/config.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\TransactionService;

if (!isset($_POST['parentTransactionId'])) {
    ?>
    <form action="cancel.php" method="post">
        <div class="form-group">
            <label for="parentTransactionId">Transaction ID to be refunded:</label><br>
            <input id="parentTransactionId" name="parentTransactionId" class="form-control" />
            <div class="col-sm-6" style="margin: 0; padding: 0;">
                <label data-i18n="amount">Amount</label>
                <small data-i18n="optional" class="pull-right">Mandatory</small>
                <div class="form-group has-feedback">
                    <input class="form-control" id="amount" name="amount" placeholder="Amount"><i
                            class="form-control-feedback fv-icon-no-label" data-fv-icon-for="amount"></i>
                </div>
            </div>
            <div class="col-sm-6" style="margin-top: 25px;">
                <div class="form-group has-select-feedback has-feedback">
                    <select id="currency" class="form-control" name="currency">
                        <option value="" disabled="true" selected="true">Currency</option>
                        <option value="SGD">SGD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Refund</button>
    </form>
<?php
} else {
// ### Transaction
    $transaction = new CreditCardTransaction();
    $transaction->setParentTransactionId($_POST['parentTransactionId']);
    $transaction->setAmount(new Amount(floatval($_POST['amount']), $_POST['currency']));

// ### Transaction Service

// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
    $transactionService = new TransactionService($config);
    $response = $transactionService->cancel($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        echo 'Payment successfully cancelled.<br>';
        echo getTransactionLink($baseUrl, $response);
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
}

require __DIR__ . '/../../inc/footer.php';
