<?php
/**
 * Shop System Plugins:
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
        'Redirect to 3-D Secure page' => "//*[@class='btn btn-primary']"
    );
}
