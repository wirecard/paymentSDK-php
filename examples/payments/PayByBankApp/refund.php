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

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Browser;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Device;
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
// ### Transaction related objects

    $amount = new Amount(1.23, 'GBP');

// The account holder (first name, last name) is required.
    $accountHolder = new AccountHolder();
    $accountHolder->setLastName('Doe');
    $accountHolder->setFirstName('Jane');

// Create the mandatory fields needed for Pay by Bank app(merchant string, transaction type, Delivery type).
    $customFields = new CustomFieldCollection();
    $customFields->add(prepareCustomField('zapp.in.RefundReasonType', 'LATECONFIRMATION'));
    $customFields->add(prepareCustomField('zapp.in.RefundMethod', 'BACS'));

// Create a consumer device.
    $device = new Device();
    $device->setType("pc");
    $device->setOperatingSystem("windows");

// Set Browser
    $browser = new Browser();
    $browser->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0');
    $browser->setTimezone('+01:00');
    $browser->setScreenResolution('1920*1080');
// ### Transaction

// The Pay by Bank app transaction holds all transaction relevant data for the payment process.
    $transaction = new PayByBankAppTransaction();
    $transaction->setAmount($amount);
    $transaction->setBrowser($browser);
    $transaction->setParentTransactionId($_POST['parentTransactionId']);
    $transaction->setDevice($device);
    $transaction->setAccountHolder($accountHolder);
    $transaction->setCustomFields($customFields);

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

require __DIR__ . '/../../inc/footer.php';
