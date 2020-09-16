<?php

require __DIR__ . '/../../configuration/config.php';
require __DIR__ . '/../../inc/header.php';

?>
    <script>
        // Define initial variables for google pay
        let googlePayValues = {
            totalPrice: '<?= $googlePayTotalPrice ?>',
            currencyCode: '<?= $googlePayCurrencyCode ?>',
            countryCode: '<?= $googlePayCountryCode ?>',
            googlePayMAID: '<?= $googlePayMAID; ?>',
            gateway: 'wirecard',
            buttonSelector: '#google-pay-button-container',
            callbackFunction: function (paymentToken) {
                document.getElementById('googlePayPaymentToken').value = paymentToken;
                document.getElementById('googlePayPaymentForm').submit();
            }
        };
    </script>
    <script src="google-pay.js"></script>
    <script async src="//pay.google.com/gp/p/js/pay.js" onload="googlePayInit()"></script>

    <p>Default configuration is for testing of non tokenized cards. If you need, you can change it in <code>examples/configuration/config.php</code>.</p>

    <div id="#google-pay-button-container"></div>

    <!-- Form for processing payment to SDK-->
    <form action="pay.php" method="post" id="googlePayPaymentForm">
        <input type="hidden" id="googlePayPaymentToken" name="paymentToken">
    </form>
<?php

require __DIR__ . '/../../inc/footer.php';

