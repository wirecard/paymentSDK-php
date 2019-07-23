<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Custom fields

// It is possible to send data to Wirecard which will also be in the response.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../common.php';
require __DIR__ . '/../../configuration/config.php';
require __DIR__ . '/../header.php';

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
$notificationUrl = getUrl('notify.php');

// ### Custom fields

// The custom fields are set as an special object. The data are sent with the request and
// then returned with the response. Each custom field is prefixed with 'paysdk_',
// i.e. the custom field 'special1' will be sent and returned as 'paysdk_special1'.
$special1 = new CustomField('special1', 'abc123');
$customFields = new CustomFieldCollection();
$customFields->add($special1);


// ### Transaction

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


// ### Response handling

// In this example we output our custom parameter we sent in request from our response.
// Parameter with name _special1_ with an expected value of _abc123_
if ($response instanceof InteractionResponse) {
    echo 'Custom field "special1" was set to: ' . $response->getCustomFields()->get('special1');
} else {
    echo "Transaction was not successful.";
}

require __DIR__ . '/../footer.php';
