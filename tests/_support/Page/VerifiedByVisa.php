<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class VerifiedByVisa extends Base
{
    // include url of current page
    public $URL = 'https://c3-test.wirecard.com/acssim/app/bank';

    public $elements = array(
        'Password' => "//*[@id='password']",
        'Continue' => "//*[@name='authenticate']"
    );
}
