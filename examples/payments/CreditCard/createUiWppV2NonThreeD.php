<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Credit Card UI WPPv2 creation

// Since the credit card data needs to be sent directly to Wirecard, you need to invoke the creation of a special form
// for entering the credit card data. This form is created via a javascript. Additional processing also needs
// to take place on the client-side, so that the credit card data is not processed and/or stored anywhere else.

// ## Required libraries and objects
// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/config.php';
require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Example\Constants\Url;

// ## Transaction

// ### Transaction Service
// The _TransactionService_ is used to generate the request data needed for the generation of the UI.
$transactionService = new TransactionService($config);

$amount = new Amount(25.00, 'EUR');
$orderNumber = 'A2';

// ### Basket items
// A Basket contains one or more items.

// For each item you have to set some properties as described here.
// Required: name, price, quantity, article number, tax rate.
$item1 = new \Wirecard\PaymentSdk\Entity\Item('Item 1', new Amount(10, 'EUR'), 1);
$item1->setArticleNumber('A1');
$item1->setTaxRate(10.0);

$item2 = new \Wirecard\PaymentSdk\Entity\Item('Item 2', new Amount(10, 'EUR'), 2);
$item2->setArticleNumber('B2');
$item2->setTaxRate(20.0);

// Create a basket to store the items.
$basket = new \Wirecard\PaymentSdk\Entity\Basket();
$basket->add($item1);
$basket->add($item2);

// #### Account holder with address
$address = new \Wirecard\PaymentSdk\Entity\Address('DE', 'Berlin', 'Teststrasse');
$address->setPostalCode('13353');

$accountHolder = new \Wirecard\PaymentSdk\Entity\AccountHolder();
$accountHolder->setEmail('john.doe@test.com');
$accountHolder->setPhone('03018425165');
$accountHolder->setDateOfBirth(new \DateTime('1973-12-07'));
$accountHolder->setAddress($address);

// ### Basic CreditCardTransaction
// Create a CreditCardTransaction with all parameters which should be sent in the initial transaction.
// The fields will be mapped for the javascript request.
$transaction = new CreditCardTransaction();
$transaction->setConfig($creditcardConfig);
$transaction->setAmount($amount);
$transaction->setNotificationUrl(Url::NOTIFICATION_URL);

$redirects = new \Wirecard\PaymentSdk\Entity\Redirect(
    getUrl(Url::SUCCESS_URL),
    getUrl(Url::CANCEL_URL),
    getUrl(Url::FAILURE_URL)
);

$transaction->setRedirect($redirects);
$transaction->setBasket($basket);
$transaction->setOrderNumber($orderNumber);
$transaction->setAccountHolder($accountHolder);
$transaction->setShipping($accountHolder);

// Send custom fields for CreditCard transactions
$custom_fields = new \Wirecard\PaymentSdk\Entity\CustomFieldCollection();
$custom_fields->add( new \Wirecard\PaymentSdk\Entity\CustomField( 'orderId', '123' ) );
$transaction->setCustomFields( $custom_fields );

?>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" type="application/javascript"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <?php
    // This library is needed to generate the UI and to get a valid token ID.
    ?>
    <script src="<?= $baseUrlWppv2 ?>/loader/paymentPage.js" type="text/javascript"></script>
    <div class="row">
        <div class="col-sm-12">
            <a href="https://doc.wirecard.com/WPP.html" target="_blank"><h3>WPP v2</h3></a>
        </div>
    </div>
    <form id="payment-form" method="post" action="reserve.php">
        <?php
        // The data, which is returned from the credit card UI, needs to be sent on submitting the form.
        // In this example this is facilitated via a hidden form field.
        ?>
        <?php
        // ### Render the form

        // The javascript library needs a div which it can fill with all credit card related fields.
        ?>
        <div id="creditcard-form-div"></div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
<script type="application/javascript">

    // This function will render the credit card UI in the specified div.
    WPP.seamlessRender({

        <?php
        // We can send additional fields if we need to. E.g. shopOrderId
        $additionalData = ['shopOrderId' => 53];
        ?>

        // We fill the _requestData_ with the return value
        // from the `getCreditCardUiWithData` method of the `transactionService` which expects a transaction with all desired parameters.
        requestData: <?= $transactionService->getCreditCardUiWithData($transaction, 'authorization', 'en'); ?>,
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

        // We check if the response fields are already set.
        if ($("input#jsresponse").length ) {
            console.log('Sending the following request to your server..');
            console.log($(event.target).serialize());
        } else {
            // If not, we will prevent the submission of the form and submit the form of credit card UI instead.
            event.preventDefault();

            WPP.seamlessSubmit({
                onSuccess: setParentTransactionId,
                onError: logCallback
            })
        }
    }

    // If the submit to Wirecard is successful, `seamlessSubmitForm` will set the form fields and submit your form to
    // to your server.
    function setParentTransactionId(response) {
        var form = $('#payment-form');
        for(var key in response){
            if(response.hasOwnProperty(key)) {
                form.append("<input type='hidden' name='" + key + "' value='" + response[key] + "'>");
            }
        }
        form.append("<input id='jsresponse' type='hidden' name='jsresponse' value='true'>");
        form.submit();
    }

</script>
<?php
require __DIR__ . '/../../inc/footer.php';
