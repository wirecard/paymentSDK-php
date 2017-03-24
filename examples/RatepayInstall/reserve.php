<?php
// # Ratepay installment reserve transaction
// This example displays the usage of reserve method for payment method Ratepay installment.

// ## Required objects
// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\RatepayInstallTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// #### Basic configuration
// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// #### Ratepay installment
// Create and add a configuration object with the Ratepay installment settings
$ratepayInstallMAID = '73ce088c-b195-4977-8ea8-0be32cca9c2e';
$ratepayInstallKey = 'd92724cf-5508-44fd-ad67-695e149212d5';

$ratepayInstallConfig = new Config\PaymentMethodConfig(RatepayInstallTransaction::NAME, $ratepayInstallMAID, $ratepayInstallKey);
$config->add($ratepayInstallConfig);

// ### Transaction related objects
// Use the money object as amount which has to be payed by the consumer.
$amount = new Money(2400, 'EUR');

// The redirect URLs determine where the consumer should be redirected by Ratepay installment after the reserve.
$redirectUrls = new Redirect(getUrl('return.php?status=success'), getUrl('return.php?status=cancel'));

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// The order number
$orderNumber = 'A2';

// #### Order items
// Create your items.
$item1 = new \Wirecard\PaymentSdk\Entity\Item('Item 1', new Money(400, 'EUR'), 1);
$item1->setArticleNumber('A1');
$item1->setTaxRate(0.1);

$item2 = new \Wirecard\PaymentSdk\Entity\Item('Item 2', new Money(1000, 'EUR'), 2);
$item2->setArticleNumber('B2');
$item2->setTaxRate(0.2);

// Create an item collection to store the items.
$itemCollection = new \Wirecard\PaymentSdk\Entity\ItemCollection();
$itemCollection->add($item1);
$itemCollection->add($item2);


// #### Account holder with address
$address = new \Wirecard\PaymentSdk\Entity\Address('DE', 'Berlin', 'Berlin');
$address->setPostalCode('13353');

$accountHolder = new \Wirecard\PaymentSdk\Entity\AccountHolder();
$accountHolder->setFirstName('John');
$accountHolder->setLastName('Constantine');
$accountHolder->setEmail('john.doe@test.com');
$accountHolder->setPhone('03018425165');
$accountHolder->setDateOfBirth(new \DateTime('1973-12-07'));
$accountHolder->setAddress($address);


// ## Transaction

// The Ratepay installment transaction holds all transaction relevant data for the reserve process.
$tx = new RatepayInstallTransaction();
$tx->setNotificationUrl($notificationUrl);
$tx->setRedirect($redirectUrls);
$tx->setAmount($amount);
$tx->setItemCollection($itemCollection);
$tx->setOrderNumber($orderNumber);
$tx->setAccountHolder($accountHolder);

// ### Transaction Service
// The service is used to execute the reserve operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($tx);


// ## Response handling

// The response of the service must be handled depending on it's class
// In case of an `InteractionResponse`, a browser interaction by the consumer is required
// in order to continue the reserve process. In this example we proceed with a header redirect
// to the given _redirectUrl_. IFrame integration using this URL is also possible.
if ($response instanceof InteractionResponse) {
    header('location: ' . $response->getRedirectUrl());
    exit;
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
