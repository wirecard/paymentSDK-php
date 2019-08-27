<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardSuccess extends Base
{
    //include url of current page
    public $URL = '/CreditCard/return.php?status=success';

    //page specific text that can be found in the URL
    public $pageSpecific = 'success';

    //page elements
    public $elements = array(
        'Payment successfully completed.' => "Payment successfully completed.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a"
    );
}
