<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardSuccess extends Base
{
    // include url of current page
    public $URL = '/CreditCard/return.php?status=success';

    public $page_specific = 'success';

    public $elements = array(
        'Payment successfully completed.' => "Payment successfully completed.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//*[@id='overrides']/div/a"

    );
}
