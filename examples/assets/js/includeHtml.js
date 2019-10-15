/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

function includeHTML()
{
    var z, i, elmnt, file, xhttp;
    /*loop through a collection of all HTML elements:*/
    z = document.getElementsByTagName("*");
    for (i = 0; i < z.length; i++) {
        elmnt = z[i];
        /*search for elements with a certain atrribute:*/
        file = elmnt.getAttribute("include-html");
        if (file) {
            /*make an HTTP request using the attribute value as the file name:*/
            xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState === 4) {
                    if (this.status === 200) {
                        elmnt.innerHTML = this.responseText;
                    }
                    if (this.status === 404) {
                        elmnt.innerHTML = "Page not found.";
                    }
                    /*remove the attribute, and call this function once more:*/
                    elmnt.removeAttribute("include-html");
                    includeHTML();
                }
            };
            xhttp.open("GET", file, true);
            xhttp.send();
            /*exit the function:*/
            return;
        }
    }
}

/**
 * Include all payment menus
 */
function includePaymentMenu()
{
    /**
     * List of all available payment menus
     * It should be the same as order name in payments folder
     * @type {string[]}
     */
    var payments = [
        'AlipayCrossborder',
        'Bancontact',
        'CreditCard',
        'Eps',
        'Giropay',
        'iDEAL',
        'Masterpass',
        'PayByBankApp',
        'PoiPia',
        'PayPal',
        'Payolution_BtwoB',
        'Payolution_Invoice',
        'Paysafecard',
        'Przelewy24',
        'RatePAY_DirectDebit',
        'RatePAY_Installment',
        'RatePAY_Invoice',
        'SepaDirectDebit',
        'SepaCredit',
        'SepaBtwoB',
        'Sofort',
        'UPOP',
        'WeChat'
    ];

    var element = document.getElementById('payments');
    var renderedHtml = '';
    payments.forEach(function (payment, index) {
        var file = 'payments/' + payment + '/menu.html';
        if (index % 3 === 0) {
            renderedHtml += '<div class="row">';
        }
        renderedHtml += '<div include-html="' + file + '"></div>';
        if (index % 3 === 2 || index === (payments.length - 1)) {
            renderedHtml += '</div>';
        }
    });
    element.innerHTML = renderedHtml;
}

/**
 * Flash paypal button
 */
function flashPayPalLink()
{
    var link = $("#paypal-pay-link"), counter = 0;
    var interval = setInterval(function () {
        counter++;
        link.toggleClass("flash");
        if (counter > 6) {
            clearInterval(interval);
            link.removeClass("flash");
        }
    }, 500);
}