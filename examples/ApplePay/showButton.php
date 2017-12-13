<?php

// # Credit Card UI creation

// Since the credit card data needs to be sent directly to Wirecard, you need to invoke the creation of a special form
// for entering the credit card data. This form is created via a javascript. Additional processing also needs
// to take place on the client-side, so that the credit card data is not processed and/or stored anywhere else.

// ## Required libraries and objects
// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/config.php';

require __DIR__ . '/../inc/header.php';
use Wirecard\PaymentSdk\TransactionService;

// ## Transaction

// ### Transaction Service
// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);

if (isset($_GET['validationUrl'])){
    validateMerchant($config, $_GET['validationUrl']);
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


<?php

/**
 * @param \Wirecard\PaymentSdk\Config\Config $config
 * @param $url
 */
function validateMerchant($config, $validation_url) {
    if( "https" == parse_url($validation_url, PHP_URL_SCHEME) && substr( parse_url($validation_url, PHP_URL_HOST), -10 )  == ".apple.com" ){

        // create a new cURL resource
        $ch = curl_init();

        $data = '{"merchantIdentifier":"'.$config->get('applepay')->getMerchantIdentifier().'", "domainName":"'.PRODUCTION_DOMAINNAME.'", "displayName":"'.PRODUCTION_DISPLAYNAME.'"}';

        curl_setopt($ch, CURLOPT_URL, $validation_url);
        curl_setopt($ch, CURLOPT_SSLCERT, PRODUCTION_CERTIFICATE_PATH);
        curl_setopt($ch, CURLOPT_SSLKEY, PRODUCTION_CERTIFICATE_KEY);
        curl_setopt($ch, CURLOPT_SSLKEYPASSWD, PRODUCTION_CERTIFICATE_KEY_PASS);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if(curl_exec($ch) === false)
        {
            echo '{"curlError":"' . curl_error($ch) . '"}';
        }

        // close cURL resource, and free up system resources
        curl_close($ch);
    }
}