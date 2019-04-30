<?php
// # Basket data

// The data, which items were purchased is required for some payment methods like RatePAY invoice / installment.
// At some payment methods like PayPal this information can be displayed on the payment processing page.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';
//Header design
require __DIR__ . '/../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// For more information on these parameters visit the PayPal examples.
$redirectUrls = new Redirect(
    getUrl('../PayPal/return.php?status=success'),
    getUrl('../PayPal/return.php?status=cancel')
);
$notificationUrl = getUrl('../PayPal/notify.php');

// ### Basket items
// A Basket contains one or more items.

// For each item you have to set some properties as described here.
// The name, the price and the quantity are required,
// the article number, the description, the tax rate and the tax amount are optional.
$item1 = new \Wirecard\PaymentSdk\Entity\Item('Item 1', new Amount(2.59, 'EUR'), 1);
$item1->setArticleNumber('A1');
$item1->setDescription('My first item');
$item1->setTaxRate(20.0);

$item2 = new \Wirecard\PaymentSdk\Entity\Item('Item 2', new Amount(5, 'EUR'), 2);
$item2->setArticleNumber('B2');
$item2->setDescription('My second item');
$item2->setTaxRate(10.0);

// The items are all stored in a `Basket` object.
$basket = new \Wirecard\PaymentSdk\Entity\Basket();
$basket->add($item1);
$basket->add($item2);

// The amount needs to be equal to the total amount for the order items.
$amount = new Amount(12.59, 'EUR');


// ## Transaction

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
//Footer design
require __DIR__ . '/../inc/footer.php';
