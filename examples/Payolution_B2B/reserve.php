<?php
// # Payolution invoice reserve transaction

// This example displays the usage of reserve method for payment method Payolution invoice.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/globalconfig.php';
//Header design
require __DIR__ . '/../inc/header.php';

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PayolutionB2BTransaction;
use Wirecard\PaymentSdk\TransactionService;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\CompanyInfo;

// ### Transaction related objects

// Use the amount object as amount which has to be paid by the consumer.
$amount = new Amount(700, 'EUR');

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// The order number
$orderNumber = '123456';

// Account holder with address
$accountHolder = new AccountHolder();
$accountHolder->setFirstName('Pavariotti');
$accountHolder->setLastName('Agnes');
$accountHolder->setEmail('agnes@example.com');
$accountHolder->setPhone('+4311234567');

$address = new Address('AT', 'Wien', 'Platz der himmlischen Ruhe 12');
$address->setPostalCode('1110');
$accountHolder->setAddress($address);

// companyInfo with at least the company name, other info are optionally
$companyInfo = new CompanyInfo('Modern art technology Inc.');
$companyInfo->setCompanyUid('ATU123456');
$companyInfo->setCompanyTradeRegisterNumber('FN 1234567');
$companyInfo->setCompanyRegisterKey('Additional registration information about consumer');

// ### Transaction

// The Payolution B2B transaction holds all transaction relevant data for the reserve process.
$transaction = new PayolutionB2BTransaction();
$transaction->setNotificationUrl($notificationUrl);
$transaction->setAmount($amount);
$transaction->setOrderNumber($orderNumber);
$transaction->setAccountHolder($accountHolder);
$transaction->setCompanyInfo($companyInfo);

// ### Transaction Service

// The service is used to execute the reserve operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);

// ## Response handling

// The response of the service must be handled depending on it's class.
if ($response instanceof SuccessResponse) {
    echo 'Reservation successfully completed.<br>';
    echo getTransactionLink($baseUrl, $response);
    ?>
    <form action="pay-based-on-reserve.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <button type="submit" class="btn btn-primary">Capture the reservation</button>
    </form>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <button type="submit" class="btn btn-primary">Cancel the reservation</button>
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
//Footer design
require __DIR__ . '/../inc/footer.php';
