<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Payment after a reservation

// Enter the ID of the successful reserve transaction and start a pay transaction with it.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PayolutionBtwobTransaction;
use Wirecard\PaymentSdk\TransactionService;

if (!isset($_POST['parentTransactionId'])) {
    ?>
    <form action="pay-based-on-reserve.php" method="post">
        <div class="form-group">
            <label for="parentTransactionId">Transaction ID to capture:</label><br>
            <input id="parentTransactionId" name="parentTransactionId" class="form-control"/><br>
        </div>
        <button type="submit" class="btn btn-primary">Capture the payment</button>
    </form>
    <?php
} else {
// ## Gathering data

// ### Transaction related objects

// Use the amount object as amount which has to be paid by the consumer.
    $amount = new Amount(700, 'EUR');

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
    $notificationUrl = getUrl('notify.php');

// ### Transaction

// The Payolution invoice transaction holds all transaction relevant data for the reserve process.
    $transaction = new PayolutionBtwobTransaction();
    $transaction->setNotificationUrl($notificationUrl);
    $transaction->setAmount($amount);
    $transaction->setParentTransactionId($_POST['parentTransactionId']);

// ## Perform the call using _Transaction Service_

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
        <form action="refund.php" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <button type="submit" class="btn btn-primary">Refund</button>
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


require __DIR__ . '/../../inc/footer.php';
