<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # SEPA return after transaction

// The consumer gets redirected to this page after a SEPA transaction.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction

// ### Transaction Service

// The `TransactionService` is used to determine the response from the service provider.
$service = new TransactionService($config);
$response = $service->handleResponse($_POST);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo 'Payment successfully completed.<br>';
    echo getTransactionLink($baseUrl, $response, $config);
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <button type="submit" class="btn btn-primary">Cancel the payment</button>
    </form>
    <br>
    <br>
    <form action="pay.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <button type="submit" class="btn btn-primary">Request a new payment based on this payment</button>
    </form>
    <br>
    <form action="../SepaCredit/referencedcredit.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <div class="form-group">
            <label for="amount">Amount:</label>
            <input id="amount" name="amount" class="form-control"/>
        </div>
        <button type="submit" class="btn btn-primary">Request a credit based on this payment</button>
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
$backendService = new BackendService($config);
$transaction = new SepaDirectDebitTransaction();
$transaction->setParentTransactionId($response->getTransactionId());

// ### Retrieve possible operations for the transaction. An array of possible operations is returned
echo '<br>Possible backend operations: ' .
    print_r($backendService->retrieveBackendOperations($transaction, true), true) . '<br>';
// ### Check it the state of the transaction is final.
echo '<br>Is '. $response->getTransactionType() .' final: ' .
    printf($backendService->isFinal($response->getTransactionType())) . '<br>';
// ### Get order state of the transaction
echo '<br>Order state: ' . $backendService->getOrderState($response->getTransactionType());

//Footer design
require __DIR__ . '/../../inc/footer.php';
