<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Payolution B2B reserve transaction

// This example displays the usage of reserve method for payment method Payolution invoice.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PayolutionBtwobTransaction;
use Wirecard\PaymentSdk\TransactionService;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\CompanyInfo;

// ## Collect data

// ### Transaction related objects

// Use the amount object as amount which has to be paid by the consumer.
$amount = new Amount(700, 'EUR');

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// The order number
$orderNumber = '180703111134838';

// ### Account holder with address

// Note: birth date is not required for B2B
$accountHolder = new AccountHolder();
// The account holder last name is required.
$accountHolder->setLastName('Doe');
// The account holders first name, email, phone are optional.
// For complete list of all fields please visit https://doc.wirecard.com/RestApi_Fields.html
$accountHolder->setFirstName('John');
$accountHolder->setEmail('support4558@wirecard.at');
$accountHolder->setPhone('+180629103914690');

$address = new Address('DE', 'Traumstadt', 'Nicht versenden Strasse 42');
$address->setPostalCode('12345');
$accountHolder->setAddress($address);

// ### companyInfo with at least the company name, other info are optionally
$companyInfo = new CompanyInfo('Company Name Inc.');
$companyInfo->setCompanyUid('ATU000000');
$companyInfo->setCompanyTradeRegisterNumber('FN 00000 n');
$companyInfo->setCompanyRegisterKey('2112322');

// ### Transaction

// The Payolution B2B transaction holds all transaction relevant data for the reserve process.
$transaction = new PayolutionBtwobTransaction();
$transaction->setNotificationUrl($notificationUrl);
$transaction->setAmount($amount);
$transaction->setOrderNumber($orderNumber);
$transaction->setAccountHolder($accountHolder);
$transaction->setCompanyInfo($companyInfo);

// ## Perform the call using _Transaction Service_

// The service is used to execute the reserve operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);

// ## Response handling

// The response of the service must be handled depending on it's class.
if ($response instanceof SuccessResponse) {
    echo 'Reservation successfully completed.<br>';
    echo getTransactionLink($baseUrl, $response);
    ?>
    <br>
    <form action="pay-based-on-reserve.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <button type="submit" class="btn btn-primary">Capture</button>
    </form>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <button type="submit" class="btn btn-primary">Cancel</button>
    </form>
    <?php
// The failure state is represented by a FailureResponse object.
// In this case the returned errors should be stored in your system.
} elseif ($response instanceof FailureResponse) {
// In our example we iterate over all errors and echo them out. You should display them as
// error, warning or information based on the given severity.
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

require __DIR__ . '/../../inc/footer.php';
