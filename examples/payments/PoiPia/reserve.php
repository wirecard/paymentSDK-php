<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Payment on invoice / Payment in advance reservation

// The method `reserve` of the _transactionService_ provides the means
// to reserve an amount (also known as authorization).

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\TransactionService;
use \Wirecard\PaymentSdk\Entity\AccountHolder;

// ### Transaction related objects

// Create an amount object as amount which has to be paid by the consumer.
$amount = new Amount(70.00, 'EUR');

// Create an account holder object, which contains Payment on invoice / Payment in advance relevant fields
$accountHolder = new AccountHolder();
// The account holder last name is required for credit.
$accountHolder->setLastName('Doe');
// The account holders first name and email are optional.
// For complete list of all fields please visit https://doc.wirecard.com/RestApi_Fields.html
$accountHolder->setFirstName('John');
$accountHolder->setEmail("john.doe@wirecard.com");

// The redirect URL determines where the consumer should be redirected to
// after an approval/cancellation on the issuer's ACS page.
$redirectUrl = getUrl('return.php?status=success');

// ### Transaction

// The Payment on invoice / Payment in advance transaction contains all relevant data for the payment process.
$transaction = new PoiPiaTransaction();
$transaction->setAmount($amount);
$transaction->setAccountHolder($accountHolder);
// ### Transaction Service

// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// If a redirect of the customer is required a `FormInteractionResponse` object is returned.
if ($response instanceof SuccessResponse):
    echo 'Reservation successfully completed.<br>';
    echo getTransactionLink($baseUrl, $response);
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <button type="submit" class="btn btn-primary">Cancel the reservation</button>
    </form>
<?php
// In case of a failed transaction, a `FailureResponse` object is returned.
elseif ($response instanceof FailureResponse):
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
endif;

require __DIR__ . '/../../inc/footer.php';
