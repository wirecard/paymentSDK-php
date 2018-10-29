<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class VerifiedByVisaPage
{
    // include url of current page
    public static $URL = 'https://c3-test.wirecard.com/acssim/app/bank';

    public static $elements = array(
        'Password' => "//*[@id='password']",
        'Continue' => "//*[@name='authenticate']"
    );

    /**
     * Method page element
     *
     * @param string $name
     * @return string
     */
    public function getElement($name)
    {
        return self::$elements[$name];
    }
}
