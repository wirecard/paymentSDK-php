<?php
// # Payment after a reservation

// Enter the ID of the successful reserve transaction and start a pay transaction with it.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// The order number
$orderNumber = 'A2';

// #### Basket items

// RatePAY requires information on the purchased items.
$item1 = new \Wirecard\PaymentSdk\Entity\Item('Item 1', new Amount(400, 'EUR'), 1);
$item1->setArticleNumber('A1');
// In contrast to the [basket example](../Features/basket.html), RatePAY requires the **tax rate**.
$item1->setTaxRate(0.1);

$item2 = new \Wirecard\PaymentSdk\Entity\Item('Item 2', new Amount(1000, 'EUR'), 2);
$item2->setArticleNumber('B2');
$item2->setTaxRate(0.2);

// Create an item collection to store the items.
$itemCollection = new \Wirecard\PaymentSdk\Entity\ItemCollection();

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

// The RatePAY installment transaction holds all transaction relevant data for the reserve process.
$transaction = new RatepayInstallmentTransaction();
$transaction->setNotificationUrl($notificationUrl);
$transaction->setItemCollection($itemCollection);
$transaction->setOrderNumber($orderNumber);
$transaction->setAccountHolder($accountHolder);
if (array_key_exists('parentTransactionId', $_POST)) {
    $parentTransactionId = $_POST['parentTransactionId'];
    $transaction->setParentTransactionId($_POST['parentTransactionId']);
} else {
    $parentTransactionId = '';
};

if (array_key_exists('item_to_capture', $_POST)) {
    switch ($_POST['item_to_capture']) {
        case '1':
            $itemCollection->add($item1);
            $amount = new Amount(400, 'EUR');
            break;
        case '2':
            $itemCollection->add($item2);
            $amount = new Amount(2000, 'EUR');
            break;
        default:
            $itemCollection->add($item1);
            $itemCollection->add($item2);
            $amount = new Amount(2400, 'EUR');
    }
    $transaction->setAmount($amount);
}


// ### Transaction service

// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);

if (array_key_exists('item_to_capture', $_POST)) {
    $response = $transactionService->pay($transaction);
} else {
    $response = null;
}

// ## Select the item to capture
?>
    Select the item to capture:
    <form action="pay-based-on-reserve.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $parentTransactionId ?>"/>
        <input type="hidden" name="item_to_capture" value="all"/>
        <input type="submit" value="Capture all items">
    </form>
    <form action="pay-based-on-reserve.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $parentTransactionId ?>"/>
        <input type="hidden" name="item_to_capture" value="1"/>
        <input type="submit" value="Capture item 1">
    </form>
    <form action="pay-based-on-reserve.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $parentTransactionId ?>"/>
        <input type="hidden" name="item_to_capture" value="2"/>
        <input type="submit" value="Capture item 2">
    </form>
<?php


// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo 'Payment successfully completed.<br>';
    echo getTransactionLink($baseUrl, $response);
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="hidden" name="transaction-type" value="<?= $response->getTransactionType() ?>"/>
        <?php
        if (array_key_exists('item_to_capture', $_POST)) {
            echo sprintf('<input type="hidden" name="amount" value="%0.2f"/>', $amount->getValue());
        }
        ?>
        <input type="submit" value="Cancel the capture">
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
