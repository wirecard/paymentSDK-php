<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Basket data

// The data, which items were purchased is required for some payment methods like RatePAY invoice / installment.
// At some payment methods like PayPal this information can be displayed on the payment processing page.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../common.php';
require __DIR__ . '/../../configuration/config.php';
require __DIR__ . '/../header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Set redirect URLs for success, cancel and failure.
// From payment page you will be redirected to:
// Success URL when the payment is approved.
// Cancel URL when the user cancels the transaction on payment page.
// Failure URL when payment is not approved or the data are missing or incorrect
$redirectUrls = new Redirect(
    getUrl('../PayPal/return.php?status=success'),
    getUrl('../PayPal/return.php?status=cancel'),
    getUrl('../PayPal/return.php?status=failure')
);
$notificationUrl = getUrl('../PayPal/notify.php');

// ### Basket items
// A Basket contains one or more items.

// For each item you have to set some properties as described here.
// The name, the price and the quantity are required,
// the article number, the description, the tax rate and the tax amount are optional.
$item1 = new Item('Item 1', new Amount(2.59, 'EUR'), 1);
$item1->setArticleNumber('A1');
$item1->setDescription('My first item');
$item1->setTaxRate(20.0);

$item2 = new Item('Item 2', new Amount(5, 'EUR'), 2);
$item2->setArticleNumber('B2');
$item2->setDescription('My second item');
$item2->setTaxRate(10.0);

// The items are all stored in a `Basket` object.
$basket = new Basket();
$basket->add($item1);
$basket->add($item2);

// The amount needs to be equal to the total amount for the order items.
$amount = new Amount(12.59, 'EUR');


// ### Transaction

// The PayPal transaction holds all transaction relevant data for the reserve process.
$transaction = new PayPalTransaction();
$transaction->setNotificationUrl($notificationUrl);
$transaction->setRedirect($redirectUrls);
$transaction->setAmount($amount);

// Include the basket in the transaction.
$transaction->setBasket($basket);

// ### Transaction Service

// The service is used to execute the reserve operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);


// ## Response handling

// The response of the service must be handled depending on it's class
// In case of an `InteractionResponse`, a browser interaction by the consumer is required
// in order to continue the reserve process. In this example we proceed with a header redirect
// to the given _redirectUrl_. IFrame integration using this URL is also possible.
if ($response instanceof InteractionResponse) {
    die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");

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

require __DIR__ . '/../footer.php';
