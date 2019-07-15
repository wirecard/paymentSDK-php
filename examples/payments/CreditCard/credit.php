<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Credit to a credit card

// To transfer funds to a credit card via a credit operation, a token for the corresponding credit card is required.
// A request with the token ID and the account holder name is sent.

// ## Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/config.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Define the token for the credit card where the amount should be credited.
$tokenId = '5168216323601006';

// ### Transaction related objects

// Create a amount object as amount which has to be paid by the consumer.
$amount = new Amount(10.59, 'EUR');

$accountHolder = new AccountHolder();
// The account holder last name is required.
$accountHolder->setLastName('Doe');
// The account holders first name is optional.
// For complete list of all fields please visit https://doc.wirecard.com/RestApi_Fields.html
$accountHolder->setFirstName('Jane');

// ### Transaction

$transaction = new CreditCardTransaction();
$transaction->setAmount($amount);

// To credit an amount a token ID and the corresponding account holder is required.
$transaction->setTokenId($tokenId);
$transaction->setAccountHolder($accountHolder);

// ### Transaction Service
// The _TransactionService_ is used to generate the request data.
$transactionService = new TransactionService($config);

// The method `credit` is used to transfer funds to the credit card.
$response = $transactionService->credit($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo 'Funds successfully transfered.<br>';
    echo getTransactionLink($baseUrl, $response);
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <button type="submit" class="btn btn-primary">Cancel the credit</button>
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

require __DIR__ . '/../../inc/footer.php';
