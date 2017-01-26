<?php

// # PayPal payment transaction
// This example displays the usage payments for payment method PayPal

require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\InteractionResponse;
use Wirecard\PaymentSdk\Money;
use Wirecard\PaymentSdk\PayPalTransaction;
use Wirecard\PaymentSdk\Redirect;
use Wirecard\PaymentSdk\TransactionService;

// ### Money object
// Use the money object as amount which has to be payed by the consumer
$amount = new Money(12.59, 'EUR');

// ### Redirect urls
// The redirect urls determine where the consumer should be redirected by PayPal after approval/cancellation.
$redirectUrls = new Redirect('https://www.example.com/paypal/return/success', 'https://www.example.com/paypal/return/cancel');

// ### Notification url
// As soon as the transaction status changes a server-to-server notification will get delivered to this url
$notificationUrl = 'https://www.example.com/paypal/notify';

// ### Transaction
// The PayPal transaction holds all transaction relevant data for the payment process
$transaction = new PayPalTransaction($amount, $notificationUrl, $redirectUrls);

// ### Config
// The config object holds all interface configuration options
$config = new Config('70000-APITEST-AP', 'qD2wzQ_hrc!8', '9abf05c1-c266-46ae-8eac-7f87ca97af28', '5fca2a83-89ca-4f9e-8cf7-4ca74a02773f');

// ### Transaction Service
// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);

// ### Response handling
// The response of the service must be handled depending on it's class
// In case of an InteractionResponse browserinteraction by the consumer is required in order to continue the payment process
// In this example we proceed with a header redirect to the given redirectUrl. IFrame integration using this url is also possible
if ($response instanceof InteractionResponse) {
    header('location: ' . $response->getRedirectUrl());
    exit;
// The failure state is represented by a FailureResponse object. In this case the returned errors should be stored in your system
} else if ($response instanceof FailureResponse) {
// In our simple example we iterate over all errors and echo them out
    foreach ($response->getStatusCollection() AS $status) {
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message %s occured.<br>', $severity, $code, $description);
    }
}

