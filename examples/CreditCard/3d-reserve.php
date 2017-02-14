<?php

// # credit card amount reservation transaction (authorization)
// This example displays the usage of for payment method credit card.

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\Money;
use Wirecard\PaymentSdk\ThreeDCreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;

/**
 * @param $path
 * @return string
 */
function getUrl($path)
{
    $protocol = 'http';

    if ($_SERVER['SERVER_PORT'] === 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on')) {
        $protocol .= 's';
    }

    $host = $_SERVER['HTTP_HOST'];
    $request = $_SERVER['PHP_SELF'];
    return dirname(sprintf('%s://%s%s', $protocol, $host, $request)) . '/' . $path;
}

// ### Money object
// Use the money object as amount which has to be payed by the consumer.
$amount = new Money(12.59, 'EUR');

// ### TransactionId
// Ids from previous transactions or seamlessRenderForm success callback can be used to execute reservations
if(array_key_exists('tokenId', $_POST)) {
    $tokenId = $_POST['tokenId'];
} else {
    $tokenId = '5168216323601006';
}

// ### Redirect URLs
// The redirect URLs determine where the consumer should be redirected by PayPal after approval/cancellation.
$redirectUrl = getUrl('return.php?status=success');

// ### Notification URL
// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// ### Transaction
// The credit card transaction holds all transaction relevant data for the payment process.
$transaction = new ThreeDCreditCardTransaction($amount, $tokenId, $notificationUrl, $redirectUrl);

// ### Config
// The config object holds all interface configuration options
$config = new Config('https://api-test.wirecard.com/engine/rest/payments/', '70000-APILUHN-CARD', '8mhwavKVb91T',
    '33f6d473-3036-4ca5-acb5-8c64dac862d1', '9e0130f6-2e1e-4185-b0d5-dc69079c75cc');

// ### Transaction Service
// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);

// ### Response handling
// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.

if($response instanceof \Wirecard\PaymentSdk\FormInteractionResponse) {
    echo '<form method="' . $response->getMethod() . '" action="' . $response->getUrl() . '">';
    foreach($response->getFormFields() as $key => $value) {
        echo '<input type="hidden" name="' . $key . '" value="' . $value . '">';
    }
    echo '<input type="submit" value="Redirect to 3-D Secure page"></form>';
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    // In our example we iterate over all errors and echo them out. You should display them as error, warning or information based on the given severity.
    foreach ($response->getStatusCollection() AS $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occured.<br>', $severity, $code, $description);
    }
}
