<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;


class WirecardTransactionDetailsPage
{
    // include url of current page
    public static $URL = 'https://api-test.wirecard.com/engine/rest/merchants/';

    public static $elements = array(
        'Transaction State' => "//*[@id='mainTable']/tbody/tr[8]/td[1]",
        'SUCCESS' => "//*[@id='mainTable']/tbody/tr[8]/td[2]",
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