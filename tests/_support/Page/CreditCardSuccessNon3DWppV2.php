<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardWppV2SuccessNon3D extends Base
{
    // include url of current page
    public $URL = '/CreditCard/pay.php';

    public $page_specific = 'reserve';

    public $elements = array(
        'Reservation successfully completed..' => "Reservation successfully completed..",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a"

    );
}
