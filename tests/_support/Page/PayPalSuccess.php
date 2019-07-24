<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class PayPalSuccess extends Base
{
    // include url of current page
    public $URL = '/PayPal/return.php?status=success';

    public $page_specific = 'success';

    public $elements = array(
        'Reservation successfully completed.' => "Reservation successfully completed.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a",
        'authorization' => "//*[@id='mainTable']/tbody/tr:nth-child[7]/td.value",
        'Username' => "//*[@id='username']",
        'Password' => "//*[@id='password']",
    );
}
