<?php
// # Purchase for Przelewy24

// To reserve and capture an amount for a credit card

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';
//Header design
require __DIR__ . '/../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Redirect ;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\PtwentyfourTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create an amount object as amount which has to be paid by the consumer.
$amount = new Amount(12.59, 'PLN');

// Crate an account holder object as it is a mandatory field for Przelewy24
$accountHolder = new AccountHolder();
$accountHolder->setFirstName('Max');
$accountHolder->setLastName('Cavalera');
$accountHolder->setEmail('max.cavalera@email.com');

// The redirect URL determines where the consumer should be redirected to
// after he is finished on the
$redirectUrls = new Redirect(
    // when the payment is successful
    getUrl('return.php?status=success'),
    // when the payment failed
    getUrl('return.php?status=failure')
);

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// ## Transaction

// The Przelewy24 transaction contains all relevant data for the payment process.
$transaction = new PtwentyfourTransaction();
$transaction->setAmount($amount);
$transaction->setRedirect($redirectUrls);
$transaction->setNotificationUrl($notificationUrl);
$transaction->setAccountHolder($accountHolder);

// ### Transaction Service

// The service is used to execute the payment (authorization + capture) operation itself.
// A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);


// ## Response handling

// The response from the service can be used for disambiguation.
// Response is not final state of payment, waiting for notification
if ($response instanceof InteractionResponse):
    die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");

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
        echo sprintf('%s with code %s and message "%s" occurred.<br>', $severity, $code, $description);
    }
endif;
//Footer design
require __DIR__ . '/../inc/footer.php';
