<?php

/* Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class PayPalSuccess extends Base
{
    // include url of current page
    public $URL = '/PayPal/return.php?status=success';

    //page specific text that can be found in the URL
    public $pageSpecific = 'success';

    //page elements
    public $elements = array(
        'Reservation successfully completed.' => "Reservation successfully completed.",
        'Payment successfully completed.' => "Payment successfully completed.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a",
        'authorization' => "//*[@id='mainTable']/tbody/tr:nth-child[7]/td.value",
        'debit' => "//*[@id='mainTable']/tbody/tr:nth-child[7]/td.value",
        'Username' => "//*[@id='username']",
        'Password' => "//*[@id='password']",
    );
}
