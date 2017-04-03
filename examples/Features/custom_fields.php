<?php
// # Custom fields

// It is possible to send data to Wirecard which will also be in the response.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Define the objects which are required for the transaction.
$amount = new Amount(12.59, 'EUR');
$redirectUrls = new Redirect(getUrl('return.php?status=success'), getUrl('return.php?status=cancel'));
$notificationUrl = getUrl('notify.php');

// ### Custom fields

// The custom fields are set as an special object. The data are sent with the request and
// then returned with the response. Each custom field is prefixed with 'paysdk_',
// i.e. the custom field 'special1' will be sent and returned as 'paysdk_special1'.
$special1 = new CustomField('special1', 'abc123');
$customFields = new CustomFieldCollection();
$customFields->add($special1);


// ## Transaction

$transaction = new PayPalTransaction();
$transaction->setNotificationUrl($notificationUrl);
$transaction->setRedirect($redirectUrls);
$transaction->setAmount($amount);

// The custom fields need to be set for each transaction.
$transaction->setCustomFields($customFields);

// ### Transaction Service

// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);


// ## Response handling

// In this example we proceed with a header redirect to the given _redirectUrl_.
if ($response instanceof InteractionResponse) {
    header('location: ' . $response->getRedirectUrl());
    $response->findElement('paysdk_special1');
    exit;
} else {
    echo "Transaction was not successful.";
}
