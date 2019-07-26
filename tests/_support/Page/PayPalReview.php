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
    public $page_specific = 'checkout/review';

    public $elements = array(
        'Pay Now' => "//*[@id='confirmButtonTop']",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a"
    );
}
