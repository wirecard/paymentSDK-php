<?php

// # Sofortbanking payment transaction
// This example displays the usage payments for payment method Sofortbanking.

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
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

// ### Redirect URLs
// The redirect URLs determine where the consumer should be redirected by Sofortbanking after approval/cancellation.
$redirectUrls = new Redirect(getUrl('return.php?status=success'), getUrl('return.php?status=cancel'));

// ### Transaction
// The SofortTransaction object holds all transaction relevant data for the payment process.
// The required fields are: amount, descriptor, success and cancel redirect URL-s
$sofortTransaction = new SofortTransaction();
$sofortTransaction->setRedirect($redirectUrls);
$sofortTransaction->setDescriptor('test');
$sofortTransaction->setAmount($amount);

// ### Config
// #### Basic configuration
// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// Configuration for Sofortbanking
// Create and add a configuration object with the Sofortbanking settings
$sofortMId = 'f19d17a2-01ae-11e2-9085-005056a96a54';
$sofortSecretKey = 'ad39d9d9-2712-4abd-9016-cdeb60dc3c8f';
$sofortConfig = new Config\PaymentMethodConfig(SofortTransaction::NAME, $sofortMId, $sofortSecretKey);
$config->add($sofortConfig);


// ### Transaction Service
// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($sofortTransaction);

// ### Response handling
// The response of the service must be handled depending on it's class
// In case of an `InteractionResponse`, a browser interaction by the consumer is required
// in order to continue the payment process. In this example we proceed with a header redirect
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
        echo sprintf('%s with code %s and message "%s" occured.<br>', $severity, $code, $description);
    }
}
