<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardReserve extends Base
{
    //include url of current page
    public $URL = '/CreditCard/reserve.php';

    //page specific text that can be found in the URL
    public $pageSpecific = 'reserve';

    //page elements
    public $elements = array(
        'Redirect to 3-D Secure page' => "//*[@class='btn btn-primary']",
        'Reservation successfully completed.' => "Reservation successfully completed.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a",
        'Noted Transaction Identification' => '',
        'Cancel the payment' => "//*[@class='btn btn-primary']"
    );
}
