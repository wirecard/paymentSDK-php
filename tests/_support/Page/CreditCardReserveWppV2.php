<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardReserveWppV2 extends Base
{
    // include url of current page
    public $URL = '/CreditCard/reserve.php';

    public $page_specific = 'reserve';

    public $elements = array(
        'Redirect to 3-D Secure page' => "//*[@class='btn btn-primary']",
        'Cancel the payment' => "//*[@class='btn btn-primary']",
        'Transaction ID' => "//div[contains(@class, 'content')]/a",
    );
}
