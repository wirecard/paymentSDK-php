<?php

// # Credit Card ui creation

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\TransactionService;

// ### Config
// The config object holds all interface configuration options
$config = new Config('https://api-test.wirecard.com/engine/rest/payments/', '70000-APILUHN-CARD', '8mhwavKVb91T',
    '9105bb4f-ae68-4768-9c3b-3eda968f57ea', 'd1efed51-4cb9-46a5-ba7b-0fdc87a66544');

// ### Transaction Service
// The `TransactionService` is used to generate the requestData needed for the generation of the credit card ui
$transactionService = new TransactionService($config);

?>

<html>
<head>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" type="application/javascript"></script>

    <!--
    ### Javascript library for credit card ui
    This library is needed to generate the credit card ui and to get a valid transaction id containing the card information
    -->
    <script src="https://api-test.wirecard.com/engine/hpp/paymentPageLoader.js" type="text/javascript"></script>
</head>
<body>
<form id="payment-form" method="post">
    <!--
    ### Form field transactionId
    The transaction id which is returned from the credit card ui needs to be send with all other fields from your shop.
    This is done in this example by filling a hidden form field
    -->
    <input type="hidden" name="transactionId" id="transactionId" value="">

    <!--
    ### Credit card form div
    The javascript library needs a div which it can fill with all credit card related fields
    -->
    <div id="creditcard-form-div"></div>
    <input type="submit" value="Save">
</form>
<script type="application/javascript">
    // ### Render Form
    // This function will render the credit card ui in the div of your choice
    WirecardPaymentPage.seamlessRenderForm({
        // We fill the requestData with the return value from the `getDataForCreditCardUi` method of the transactionService
        requestData: <?= $transactionService->getDataForCreditCardUi(); ?>,
        wrappingDivId: "creditcard-form-div",
        onSuccess: logCallback,
        onError: logCallback
    });

    function logCallback(response) {
        console.log(response);
    }

    // ### Submit handler for the form
    // Before your own shop form is submitted, you should submit the credit card ui form, so that you get a transaction id which you need for the actual payment
    $('#payment-form').submit(submit);

    function submit(event) {
        // We check if the transactionId field already got a value
        if ($('#transactionId').val() == '') {
            //If not, we will prevent the form submit and do a credit card ui form submit instead
            event.preventDefault();

            WirecardPaymentPage.seamlessSubmitForm({
                onSuccess: setParentTransactionId,
                onError: logCallback
            })
        } else {
            console.log('Sending the following request to your server..')
            console.log($(event.target).serialize());
        }
    }

    // This onSuccess handler for `seamlessSubmitForm` will set the transactionId in your own form and do again a form submit which will be send to your server
    function setParentTransactionId(response) {
        $('#transactionId').val(response.transaction_id);
        $('#payment-form').submit();
    }

</script>
</body>
</html>


