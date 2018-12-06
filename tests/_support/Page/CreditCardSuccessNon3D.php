<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardSuccessNon3D extends Base
{
    // include url of current page
    public $URL = '/CreditCard/pay_tokenize.php';

    public $page_specific = 'pay_tokenize';

    public $elements = array(
        'Reservation successfully completed..' => "Reservation successfully completed..",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//*[@id='overrides']/div/a"

    );
}
