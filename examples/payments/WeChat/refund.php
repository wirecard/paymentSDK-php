<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Refunding a transaction

// To refund a transaction, a refund request with the parent transaction is sent.

// ## Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/wechatconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\WeChatTransaction;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Transaction\Operation;

if (!isset($_POST['parentTransactionId'])) {
    ?>
    <form action="refund.php" method="post">
        <div class="form-group">
            <label for="parentTransactionId">Transaction ID to refund:</label>
            <input id="parentTransactionId" name="parentTransactionId" class="form-control" />
        </div>
        <button type="submit" class="btn btn-primary">Refund</button>
    </form>
    <?php
} else {

// ### Transaction related objects

// ### Notification URL

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
    $notificationUrl = getUrl('notify.php');


// ### Transaction

// The WeChat QRPay transaction holds all transaction relevant data for the payment process.
    $transaction = new WeChatTransaction();
    $transaction->setParentTransactionId($_POST['parentTransactionId']);

// ### Transaction Service

// The service is used to execute the payment operation itself. A response object is returned.
    $transactionService = new TransactionService($config);
    $response = $transactionService->process($transaction, Operation::REFUND);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        if ($response->getTransactionType() === Transaction::TYPE_VOID_AUTHORIZATION) {
            echo 'Payment successfully refunded.<br>';
        } else {
            echo 'Funds successfully transferred.<br>';
        }
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
