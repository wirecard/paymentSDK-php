<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Credit via RatePAY

// To transfer funds via a credit operation, information on the receiver are required.
// A request is sent with the account holder information.

// ## Required objects

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\RatepayDirectDebitTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Use the amount object as amount which has to be paid by the consumer.
if (array_key_exists('amount', $_POST)) {
    $amountValue = floatval($_POST['amount']);
} else {
    $amountValue = 100;
}
if (!array_key_exists('parentTransactionId', $_POST)) {
    $_POST['parentTransactionId'] = '14c7699a-088c-4022-a173-cfc99e0db03c';
}
$amount = new Amount($amountValue, 'EUR');

// ### Notification URL

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

$credit1 = new Item('Credit 1', $amount, 1);
$credit1->setArticleNumber('C1');
$credit1->setTaxRate(0.0);

$basket = new Basket();
$basket->add($credit1);


// ### Transaction

// The RatePAY transaction holds all transaction relevant data for the payment process.
$transaction = new RatepayDirectDebitTransaction();
$transaction->setNotificationUrl($notificationUrl);
$transaction->setAmount($amount);
$transaction->setParentTransactionId($_POST['parentTransactionId']);
$transaction->setBasket($basket);

// ### Transaction Service

// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->credit($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo 'Funds successfully transferred.<br>';
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

require __DIR__ . '/../../inc/footer.php';
