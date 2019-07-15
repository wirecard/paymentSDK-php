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
require __DIR__ . '/../../configuration/globalconfig.php';
require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\TransactionService;

if (!isset($_POST['parentTransactionId'])) {
    ?>
    <form action="cancel.php" method="post">
        <div class="form-group">
            <label for="parentTransactionId">Transaction ID to cancel:</label><br>
            <input id="parentTransactionId" name="parentTransactionId" class="form-control"/><br>
        </div>
        <button type="submit" class="btn btn-primary">Cancel</button>
    </form>
<?php
} else {
// ### Transaction related objects

// Use the amount object as amount which has to be paid by the consumer.
// For void-authorization only full amount of parent transaction is supported.
    $amount = new Amount(2400, 'EUR');

// The order number
    $orderNumber = 'A2';

// #### Order items

// Create your items.
    $item1 = new Item('Item 1', new Amount(400, 'EUR'), 1);
    $item1->setArticleNumber('A1');
    $item1->setTaxRate(10.0);

    $item2 = new Item('Item 2', new Amount(1000, 'EUR'), 2);
    $item2->setArticleNumber('B2');
    $item2->setTaxRate(20.0);

// Create an item collection to store the items.
    $basket = new Basket();
    $basket->add($item1);
    $basket->add($item2);


// ### Transaction

    $transaction = new RatepayInvoiceTransaction();
    $transaction->setParentTransactionId($_POST['parentTransactionId']);
    $transaction->setAmount($amount);
    $transaction->setOrderNumber($orderNumber);
    $transaction->setBasket($basket);

// ### Transaction Service

// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
    $transactionService = new TransactionService($config);
    $response = $transactionService->cancel($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        echo 'Payment successfully cancelled.<br>';
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
