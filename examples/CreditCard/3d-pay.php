<?php

// # 3-D Secure purchase for credit card
// To reserve and capture an amount for a credit card with 3-D secure, you need to use a different transaction object
// in contrast to a transaction with SSL only.

// # Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
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

// If there was a previous transaction, use the ID of this parent transaction as reference.
$parentTransactionId = array_key_exists('parentTransactionId', $_POST) ? $_POST['parentTransactionId'] : null;

// Otherwise if a token was defined when submitting the credit card data to Wirecard via the UI, this token is used.
$tokenId = array_key_exists('tokenId', $_POST) ? $_POST['tokenId'] : null;

// To make this example usable, even is no transaction or token ID is provided, a predefined existing token ID is set.
if ($parentTransactionId === null && $tokenId === null) {
    $tokenId = '5168216323601006';
}

// The redirect URL determines where the consumer should be redirected to
// after an approval/cancellation on the issuer's ACS page.
$redirectUrl = getUrl('return.php?status=success');


// ### Config

// Since each payment method may have a different merchant ID, a config collection is created.
$configCollection = new Config\PaymentMethodConfigCollection();

// Create and add a configuration object with the settings for credit card.
// For 3-D Secure transactions the corresponding merchant account ID is required.
$ccard3dMId = '33f6d473-3036-4ca5-acb5-8c64dac862d1';
$ccard3dKey = '9e0130f6-2e1e-4185-b0d5-dc69079c75cc';
$ccard3dConfig = new Config\PaymentMethodConfig(ThreeDCreditCardTransaction::class, $ccard3dMId, $ccard3dKey);
$configCollection->add($ccard3dConfig);

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APILUHN-CARD';
$httpPass = '8mhwavKVb91T';

// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, $configCollection, 'EUR');

// The 3-D credit card transaction contains all relevant data for the payment process.
$tx = new ThreeDCreditCardTransaction();
$tx->setAmount($amount);
$tx->setTokenId($tokenId);
$tx->setTermUrl($redirectUrl);
$tx->setParentTransactionId($parentTransactionId);

// ### Transaction

// The service is used to execute the payment (authorization + capture) operation itself.
// A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($tx);

// ### Response handling

// The response from the service can be used for disambiguation.
// If a redirect of the customer is required a `FormInteractionResponse` object is returned.
if ($response instanceof FormInteractionResponse):
    // A form for redirect should be created and submitted afterwards.
    ?>
    <form method="<?= $response->getMethod(); ?>" action="<?= $response->getUrl(); ?>">
        <?php foreach ($response->getFormFields() as $key => $value): ?>
            <input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
        <?php endforeach;
        // Usually an automated transmission of the form would be made.
        // For a better demonstration and for the ease of use this automated submit
        // is replaced with a submit button.
        ?>
        <input type="submit" value="Redirect to 3-D Secure page"></form>
    <?php
elseif ($response instanceof SuccessResponse):
    echo sprintf('Payment with id %s successfully completed.<br>', $response->getTransactionId());
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="hidden" name="transaction-type" value="3d"/>
        <input type="submit" value="cancel the payment">
    </form>

    <?php
// In case of a failed transaction, a `FailureResponse` object is returned.
elseif ($response instanceof FailureResponse):
    // In our example we iterate over all errors and display them in a raw state.
    // You should handle them based on the given severity as error, warning or information.
    foreach ($response->getStatusCollection() as $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Entity\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occured.<br>', $severity, $code, $description);
    }
endif;
