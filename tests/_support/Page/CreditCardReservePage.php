<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardReservePage
{
    // include url of current page
    public static $URL = '/CreditCard/reserve.php';

    public static $elements = array(
        'Redirect to 3-D Secure page' => "//*[@class='btn btn-primary']"
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
