<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Unionpay International UI creation

// Since the credit card data needs to be sent directly to Wirecard, you need to invoke the creation of a special form
// for entering the credit card data. This form is created via a javascript. Additional processing also needs
// to take place on the client-side, so that the credit card data is not processed and/or stored anywhere else.

// ## Required libraries and objects
// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/upiconfig.php';

use Wirecard\PaymentSdk\TransactionService;


// ## Transaction

// ### Transaction Service
// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);

?>

<html>
<head>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" type="application/javascript"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <style>
        #overrides h1 {
            margin: 0px;
            font-size: 2vw;
        }

        #overrides h2 {
            padding-bottom: 10px;
            border-bottom: 1px solid #dedede;
        }

        #overrides h3 {
            min-height: 52.8px;
        }

        #overrides img {
            height: 40px;
            margin: 0px 20px;
        }

        #overrides .align-baseline {
            position: relative;
        }

        #overrides .bottom-align-text {
            position: absolute;
            bottom: -0.35vw;
            left: 235px;
        }

        #overrides .page-header {
            background-color: #002846;
            margin-top: 0px;
            padding: 40px 20px;
            color: white;
        }

        #overrides .list-group-item {
            border-radius: 0px;
            text-transform: uppercase;
            font-size: 12px;
        }

        #overrides .list-group-item:hover {
            color: #ff2014;
            background-color: #F7F7F8;
        }

        #overrides .btn-primary {
            background-color: #002846;
        }

        #overrides .btn-primary:hover {
            background-color: #414B56;
        }

    </style>
    <?php
    // This library is needed to generate the UI and to get a valid token ID.
    ?>
    <script src="https://wpp-test.wirecard.com/loader/paymentPage.js" type="text/javascript"></script>
    <style>
        #creditcard-form-div {
            height: 300px;
        }
    </style>
</head>
<body id="overrides">
<div class="container">
    <div class="page-header">
        <div class="row">
            <div class="col-sm-2">
                <img src="https://raw.githubusercontent.com/wirecard/paymentSDK-php/master/examples/src/img/wirecard_logo.png" alt="wirecard" />
            </div>
            <div class="col-sm-10 align-bottom">
                <h1>Payment SDK for PHP Examples</h1>
            </div>
        </div>
    </div>
    <div>
        <div class="row">
            <div class="col-sm-12">
                <a href="https://doc.wirecard.com/WPP.html" target="_blank"><h3>WPP v2</h3></a>
            </div>
        </div>
    </div>
    <form id="payment-form" method="post" action="reserve.php">
        <?php
        // The token ID, which is returned from the credit card UI, needs to be sent on submitting the form.
        // In this example this is facilitated via a hidden form field.
        ?>
        <input type="hidden" name="tokenId" id="tokenId" value="">
        <?php
        // ### Render the form

        // The javascript library needs a div which it can fill with all credit card related fields.
        ?>
        <div id="creditcard-form-div"></div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
<script type="application/javascript">

    // This function will render the credit card UI in the specified div.
    WPP.seamlessRender({

        // We fill the _requestData_ with the return value
        // from the `getDataForUpiUi` method of the `transactionService`.
        requestData: <?= $transactionService->getDataForUpiUi(); ?>,
        wrappingDivId: "creditcard-form-div",
        onSuccess: logCallback,
        onError: logCallback
    });

    function logCallback(response) {
        console.log(response);
    }

    // ### Submit handler for the form

    // To prevent the data to be submitted on any other server than the Wirecard server, the credit card UI form
    // is sent to Wirecard via javascript. You receive a token ID which you need for processing the payment.
    $('#payment-form').submit(submit);

    function submit(event) {

        // We check if the field for the token ID already got a value.
        if ($('#tokenId').val() == '') {

            // If not, we will prevent the submission of the form and submit the form of credit card UI instead.
            event.preventDefault();

            WPP.seamlessSubmit({
                onSuccess: setParentTransactionId,
                onError: logCallback
            })
        } else {
            console.log('Sending the following request to your server..');
            console.log($(event.target).serialize());
        }
    }

    // If the submit to Wirecard is successful, `seamlessSubmitForm` will set the field for the token ID
    // and submit your form to your server.
    function setParentTransactionId(response) {
        console.log(response);
        $('#tokenId').val(response.token_id);
        $('#payment-form').submit();
    }

</script>
</body>
</html>
