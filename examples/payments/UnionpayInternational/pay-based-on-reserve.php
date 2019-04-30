<?php
// # Payment after a reservation

// Enter the ID of the successful reserve transaction and start a pay transaction with it.

// ## Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/upiconfig.php';
//Header design
require __DIR__ . '/../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\UpiTransaction;
use Wirecard\PaymentSdk\TransactionService;

if (!isset($_POST['parentTransactionId'])) {
    ?>
    <form action="pay-based-on-reserve.php" method="post">
        <div class="form-group">
            <label for="parentTransactionId">Reserved transaction ID:</label>
            <input id="parentTransactionId" name="parentTransactionId" class="form-control"/>
        </div>
        <div class="form-group">
            <label for="amount">Amount:</label>
            <input id="amount" name="amount" class="form-control" value="1.0" />
            <small class="form-text text-muted">Please be aware that the amount for payment must not exceed the reserved amount.</small>
        </div>
        <button type="submit" class="btn btn-primary">Pay</button>
    </form>
<?php
} else {
// ## Transaction

    $transaction = new UpiTransaction();
    $transaction->setParentTransactionId($_POST['parentTransactionId']);
    if (array_key_exists('amount', $_POST)) {
        $transaction->setAmount(new Amount((float)$_POST['amount'], 'EUR'));
    }

// ### Transaction Service
// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
    $transactionService = new TransactionService($config);
    $response = $transactionService->pay($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        echo 'Payment successfully completed.<br>';
        echo getTransactionLink($baseUrl, $response);
        ?>
        <br>
        <form action="cancel.php" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <button type="submit" class="btn btn-primary">Cancel the capture</button>
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
}
//Footer design
require __DIR__ . '/../inc/footer.php';
