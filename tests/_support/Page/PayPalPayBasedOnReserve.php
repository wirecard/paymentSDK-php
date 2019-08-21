<?php

/* Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class PayPalPayBasedOnReserve extends Base
{
    //include url of current page
    public $URL = '/PayPal/pay-based-on-reserve.html';

    //page elements
    public $elements = array(
        'Reserved transaction ID' => "//*[@id='parentTransactionId']",
        'Amount' => "//*[@id='amount']",
        'Payment successfully completed.' => "Payment successfully completed.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a",
        'Noted Transaction Identification' => '',
        'Pay' => "//*[@class='btn btn-primary']",
    );
}
