<?php
// # Cancelling a transaction

// To cancel a transaction, a cancel request with the parent transaction is sent.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';
//Header design
require __DIR__ . '/../inc/header.php';

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PayByBankAppTransaction;
use Wirecard\PaymentSdk\TransactionService;

if (!isset($_POST['parentTransactionId'])) {
    ?>
    <form action="refund.php" method="post">
        <div class="form-group">
            <label for="parentTransactionId">Transaction ID to cancel:</label><br>
            <input id="parentTransactionId" name="parentTransactionId" class="form-control"/>
        </div>
        <button type="submit" class="btn btn-primary">Refund transaction</button>
    </form>
    <?php
} else {
// ## Transaction
    $transaction = new PayByBankAppTransaction();
    $transaction->setParentTransactionId($_POST['parentTransactionId']);
    $transaction->setDeviceType("pc");
    $transaction->setDeviceOperatingSystem("windows");
    $transaction->setBrowserUserAgent($_SERVER['HTTP_USER_AGENT'].'');
    $transaction->setBrowserTimezone('+01:00');
    $transaction->setBrowserScreenResolution('1280x1024');

    $accountHolder = new AccountHolder();
    $accountHolder->setLastName('Doe');
    $transaction->setAccountHolder($accountHolder);

    $customFields = new CustomFieldCollection();
    $transaction->setCustomFields($customFields);

    function addCustomField($key, $value) {
        $customField = new CustomField($key, $value);
        $customField->setPrefix("");
        return $customField;
    }

    $customFields->add(addCustomField('zapp.in.RefundReasonType', 'LATECONFIRMATION'));
    $customFields->add(addCustomField('zapp.in.RefundMethod', 'BACS'));

// ### Transaction Service

// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
    $transactionService = new TransactionService($config);
    $response = $transactionService->cancel($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        echo 'Payment successfully refunded.<br>';
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
