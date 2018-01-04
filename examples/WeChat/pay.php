<?php
// # WeChat QRPay Payments payment transaction

// This example displays the usage payments for payment method WeChat QRPay.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/wechatconfig.php';
//Header design
require __DIR__ . '/../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\SubMerchantInfo;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\WeChatTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Use the amount object as amount which has to be paid by the consumer.
$amount = new Amount(1, 'USD');


// The redirect URLs determine where the consumer should be redirected by WeChat QRPay after approval/cancellation.
$redirectUrls = new Redirect(
    getUrl('return.php?status=success'),
    getUrl('return.php?status=cancel'),
    getUrl('return.php?status=failure')
);

$notificationUrl = getUrl('notify.php');

$subMerchantInfo = new SubMerchantInfo();
$subMerchantInfo->setMerchantId('12152566');
$subMerchantInfo->setMerchantName("store name");

// ## Transaction

// The WeChat QRPay Transaction object holds all transaction relevant data for the payment process.
// The required fields are: amount, accountHolder and redirect URL-s
$transaction = new WeChatTransaction();
$transaction->setRedirect($redirectUrls);
$transaction->setNotificationUrl($notificationUrl);
$transaction->setAmount($amount);
$transaction->setSubMerchantInfo($subMerchantInfo);
$transaction->setOrderDetail('bla');

// ### Transaction Service

// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);

// ## Response handling

// The response of the service must be handled depending on it's class
// In case of an `InteractionResponse`, a browser interaction by the consumer is required
// in order to continue the payment process. In this example we proceed with a header redirect
// to the given _redirectUrl_. IFrame integration using this URL is also possible.
if ($response instanceof InteractionResponse) {
    die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");

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
//Footer design
require __DIR__ . '/../inc/footer.php';
