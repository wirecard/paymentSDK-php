<?php

/* Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class PayPalReview extends Base
{
    //page specific text that can be found in the URL
    public $pageSpecific = 'checkout';

    //page elements
    public $elements = array(
        'Continue' => "//*[@id='button']",
        'Pay Now' => "//*[@id='confirmButtonTop']",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a"
    );
}
