/**
 * Define the version of the Google Pay API referenced when creating your
 * configuration
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#PaymentDataRequest|apiVersion in PaymentDataRequest}
 */
const baseRequest = {
    apiVersion: 2,
    apiVersionMinor: 0
};

/**
 * Card networks supported by your site and your gateway
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
 * @todo confirm card networks supported by your site and gateway
 */
const allowedCardNetworks = ["AMEX", "DISCOVER", "INTERAC", "JCB", "MASTERCARD", "VISA"];

/**
 * Card authentication methods supported by your site and your gateway
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
 * @todo confirm your processor supports Android device tokens for your
 * supported card networks
 */
const allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"];


/**
 * Identify your gateway and your site's gateway merchant identifier
 *
 * The Google Pay API response will return an encrypted payment method capable
 * of being charged by a supported gateway after payer authorization
 *
 * @todo check with your gateway on the parameters to pass
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#gateway|PaymentMethodTokenizationSpecification}
 */
const tokenizationSpecification = {
    type: 'PAYMENT_GATEWAY',
    parameters: {
        'gateway': googlePayValues.gateway,
        'gatewayMerchantId': googlePayValues.googlePayMAID
    }
};

/**
 * Describe your site's support for the CARD payment method and its required
 * fields
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
 */
const baseCardPaymentMethod = {
    type: 'CARD',
    parameters: {
        allowedAuthMethods: allowedCardAuthMethods,
        allowedCardNetworks: allowedCardNetworks
    }
};


/**
 * Describe your site's support for the CARD payment method including optional
 * fields
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
 */
const cardPaymentMethod = Object.assign(
    {},
    baseCardPaymentMethod,
    {
        tokenizationSpecification: tokenizationSpecification
    }
);

/**
 * Google pay library is loaded
 */
function googlePayInit()
{
    //Init google payments client in dev mode
    const paymentsClient = new google.payments.api.PaymentsClient({environment: 'TEST'});

    // Create request for checking if google pay is supported by browser/android
    const isReadyToPayRequest = Object.assign({}, baseRequest);
    isReadyToPayRequest.allowedPaymentMethods = [baseCardPaymentMethod];

    // Check supporting of google pay
    paymentsClient.isReadyToPay(isReadyToPayRequest)
        .then(function (response) {
            if (response.result) {
                // Add a Google Pay payment button
                const button = paymentsClient.createButton({onClick: () => googlePayPaymentRequest(paymentsClient)});
                document.getElementById(googlePayValues.buttonSelector).appendChild(button);
            }
        })
        .catch(function (err) {
            // Show error in developer console for debugging
            console.error(err);
        });
}

/**
 * Start payment process
 *
 * @param paymentsClient
 */
function googlePayPaymentRequest(paymentsClient)
{
    const paymentDataRequest = Object.assign({}, baseRequest);
    paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];

    // Add order data
    paymentDataRequest.transactionInfo = {
        totalPriceStatus: 'FINAL',
        totalPrice: googlePayValues.totalPrice,
        currencyCode: googlePayValues.currencyCode,
        countryCode: googlePayValues.countryCode
    };

    // Add merchant info
    paymentDataRequest.merchantInfo = {
        merchantName: 'Example Merchant',
        // @todo a merchant ID is available for a production environment after approval by Google
        // See {@link https://developers.google.com/pay/api/web/guides/test-and-deploy/integration-checklist|Integration checklist}
        merchantId: 'TEST'
    };

    paymentsClient.loadPaymentData(paymentDataRequest).then(function (paymentData) {
        // Show returned data in developer console for debugging
        console.log(paymentData);
        // Pass payment token to your gateway to process payment
        paymentToken = paymentData.paymentMethodData.tokenizationData.token;
        // Call callback function
        googlePayValues.callbackFunction(paymentToken);
    }).catch(function (err) {
        // Show error in developer console for debugging
        console.error(err);
    });
}