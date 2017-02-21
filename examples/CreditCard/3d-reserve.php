<?php

// # Reservation for credit card with 3-D secure
// To reserve an amount for a credit card with 3-D secure, you need to use a different transaction object.

// # Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\FormInteractionResponse;
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

// Create a money object as amount which has to be payed by the consumer.
$amount = new Money(12.59, 'EUR');

// Tokens from a successful _seamlessRenderForm_ callback can be used to execute reservations.
$tokenId = array_key_exists('tokenId', $_POST) ? $_POST['tokenId'] : '5168216323601006';

// The redirect URL determines where the consumer should be redirected to after an approval/cancellation on the issuer's ACS page.
$redirectUrl = getUrl('return.php?status=success');

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// An object containing the data regarding the options of the interface is needed.
$config = new Config('https://api-test.wirecard.com/engine/rest/payments/', '70000-APILUHN-CARD', '8mhwavKVb91T',
// For 3-D Secure transactions a different merchant account ID is required than the previously executed _seamlessRenderForm_.
    '33f6d473-3036-4ca5-acb5-8c64dac862d1', '9e0130f6-2e1e-4185-b0d5-dc69079c75cc');

// The 3-D credit card transaction contains all relevant data for the payment process.
$transaction = new ThreeDCreditCardTransaction($amount, $tokenId, $notificationUrl, $redirectUrl);


// ### Transaction

// The service is used to execute the reservation (authorization) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);

// ### Response handling

// The response from the service can be used for disambiguation.
// If a redirect of the customer is required a `FormInteractionResponse` object is returned.
if($response instanceof FormInteractionResponse):
    // A form for redirect should be created and submitted afterwards.
    ?>
    <form method="<?= $response->getMethod(); ?>" action="<?= $response->getUrl(); ?>">
    <?php foreach($response->getFormFields() as $key => $value): ?>
        <input type="hidden" name="<?= $key ?>" value="<?= $value ?>">;
    <?php endforeach;
    // For a better demonstration and for the ease of use the automatic submit was replaced with a submit button.
    ?>
    <input type="submit" value="Redirect to 3-D Secure page"></form>;
<?php
// In case of a failed transaction, a `FailureResponse` object is returned.
elseif ($response instanceof FailureResponse):
    // In our example we iterate over all errors and display them in a raw state.
    // You should handle them based on the given severity as error, warning or information.
    foreach ($response->getStatusCollection() AS $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occured.<br>', $severity, $code, $description);
    }
endif;
