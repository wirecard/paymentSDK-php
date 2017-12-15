<?php

// # Credit Card UI creation

// Since the credit card data needs to be sent directly to Wirecard, you need to invoke the creation of a special form
// for entering the credit card data. This form is created via a javascript. Additional processing also needs
// to take place on the client-side, so that the credit card data is not processed and/or stored anywhere else.

// ## Required libraries and objects
// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Transaction\ApplePayTransaction;
use \Wirecard\PaymentSdk\Entity\Amount;

// ## Transaction

// ### Transaction Service
// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);

/** @var \Wirecard\PaymentSdk\Config\ApplePayConfig $applePayConfig */
$applePayConfig = $config->get('applepay');

if (isset($_GET['validationUrl'])) {
    validateMerchant($transactionService, $_GET['validationUrl']);
    die();
} elseif (isset($_GET['paymentToken'])) {
    $data = $_GET['paymentToken'];
    $applePayTransaction = new ApplePayTransaction();
    $applePayTransaction->setCryptogram(base64_encode($data));
    $applePayTransaction->setAmount(new Amount(42.0, 'EUR'));
    sendPaymentRequest($transactionService, $applePayTransaction);
    die();
} else {
    require __DIR__ . '/../inc/header.php';
}
?>

    <style>
        #applePay {
            width: 150px;
            height: 50px;
            display: none;
            border-radius: 5px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 20px;
            background-image: -webkit-named-image(apple-pay-logo-white);
            background-position: 50% 50%;
            background-color: black;
            background-size: 60%;
            background-repeat: no-repeat;
        }
    </style>

    <div>
        <button type="button" id="applePay" onclick="wirecardApplePay()" onload=""></button>
        <p style="display:none" id="got_notactive">ApplePay is possible on this browser, but not currently
            activated.</p>
        <p style="display:none" id="notgot">ApplePay is not available on this browser</p>
        <p style="display:none" id="success">Test transaction created successfully.</p>
    </div>

    <script>
        if (window.ApplePaySession) {
            var merchantIdentifier = '<?=$applePayConfig->getMerchantIdentifier()?>';
            var promise = ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);
            promise.then(function (canMakePayments) {
                if (canMakePayments) {
                    // show button, payments can be made
                    document.getElementById("applePay").style.display = "block";
                } else {
                    // hide button, payments are disabled on the machine
                    document.getElementById("got_notactive").style.display = "block";
                }
            });
        } else {
            // browser doesn't support applePay
            document.getElementById("notgot").style.display = "block";
        }

        function wirecardApplePay() {
            var paymentRequest = {
                currencyCode: 'EUR',
                countryCode: 'AT',
                total: {
                    label: '<?=$applePayConfig->getShopName()?>',
                    amount: 42
                },
                supportedNetworks: ['amex', 'masterCard', 'visa'],
                merchantCapabilities: ['supports3DS', 'supportsEMV', 'supportsCredit', 'supportsDebit']
            };

            var session = new ApplePaySession(1, paymentRequest);

            // Merchant Validation
            session.onvalidatemerchant = function (event) {
                var promise = performValidation(event.validationURL);
                promise.then(function (merchantSession) {
                    session.completeMerchantValidation(merchantSession);
                });
            };

            function performValidation(valURL) {
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.onload = function () {
                        resolve(JSON.parse(this.responseText));
                    };
                    xhr.onerror = reject;
                    xhr.open('GET', '?validationUrl=' + valURL);
                    xhr.send();
                });
            }

            session.onpaymentauthorized = function (event) {
                var promise = sendPaymentToken(event.payment.token);
                promise.then(function (success) {
                    var status;
                    if (success) {
                        status = ApplePaySession.STATUS_SUCCESS;
                        document.location.href = success;
                    } else {
                        status = ApplePaySession.STATUS_FAILURE;
                    }
                    session.completePayment(status);
                });
            };

            function sendPaymentToken(paymentToken) {
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.onload = function () {
                        resolve(this.responseText);
                    };
                    xhr.onerror = reject;
                    xhr.open('GET', '?paymentToken='+JSON.stringify(paymentToken));
                    xhr.send();
                });
            }

            session.oncancel = function (event) {
                // payment was canceled
            };

            session.begin();
        }
    </script>

<?php

/**
 * @param TransactionService $transactionService
 * @param $validation_url
 * @internal param $url
 */
function validateMerchant($transactionService, $validation_url)
{
    echo $transactionService->validateMerchant($validation_url);
}

/**
 * this method is called via ajax
 *
 * @param TransactionService $transactionService
 * @param ApplePayTransaction $transaction
 */
function sendPaymentRequest($transactionService, $transaction)
{
    echo $transactionService->reserve($transaction)->getRedirectUrl();
}