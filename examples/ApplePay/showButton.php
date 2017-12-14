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

// ## Transaction

// ### Transaction Service
// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);

/** @var \Wirecard\PaymentSdk\Config\ApplePayConfig $applePayConfig */
$applePayConfig = $config->get('applepay');

if (isset($_GET['validationUrl'])) {
    validateMerchant($config, $_GET['validationUrl']);
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
        <p style="display:none" id="success">Test transaction completed, thanks. <a href="/">reset</a>
        </p>
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
                    logit('ApplePay is possible on this browser, but not currently activated.');
                }
            });
        } else {
            // browser doesn't support applePay
            logit('ApplePay is not available on this browser');
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
                        document.getElementById("applePay").style.display = "none";
                        document.getElementById("success").style.display = "block";
                    } else {
                        status = ApplePaySession.STATUS_FAILURE;
                    }
                    session.completePayment(status);
                });
            };

            function sendPaymentToken(paymentToken) {
                logit(paymentToken);
                // send payment to the wirecard server (ajax wise) with this token
                // if ajax is successful, resolve(true) otherwise reject
            }

            session.oncancel = function (event) {
                // payment was canceled
            };

            session.begin();
        }

        function logit(data) {
            console.log(data);

        }
        ;
    </script>

<?php

/**
 * @param \Wirecard\PaymentSdk\Config\Config $config
 * @param $validation_url
 * @internal param $url
 */
function validateMerchant($config, $validation_url)
{
    if ("https" == parse_url($validation_url, PHP_URL_SCHEME) && substr(parse_url($validation_url, PHP_URL_HOST),
            -10) == ".apple.com"
    ) {
        /** @var \Wirecard\PaymentSdk\Config\ApplePayConfig $applePayConfig */
        $applePayConfig = $config->get('applepay');
        // create a new cURL resource
        $ch = curl_init();

        $data = '{"merchantIdentifier":"' . $applePayConfig->getMerchantIdentifier() . '", "domainName":"' . $applePayConfig->getDomainName() . '", "displayName":"' . $applePayConfig->getShopName() . '"}';

        curl_setopt($ch, CURLOPT_URL, $validation_url);
        curl_setopt($ch, CURLOPT_SSLCERT, $applePayConfig->getSslCertificatePath());
        curl_setopt($ch, CURLOPT_SSLKEY, $applePayConfig->getSslCertificateKey());
        curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $applePayConfig->getSslCertificatePassword());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if (curl_exec($ch) === false) {
            echo '{"curlError":"' . curl_error($ch) . '"}';
        }

        // close cURL resource, and free up system resources
        curl_close($ch);
    }
}

function sendPaymentRequest(){

}