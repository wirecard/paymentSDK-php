<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCancelPage
{
    // include url of current page
    public static $URL = '/CreditCard/cancel.php';

    public static $elements = array(
        'Transaction ID to be refunded' => "//*[@id='parentTransactionId']",
        'Refund' => "//*[@class='btn btn-primary']",
        '"Payment successfully cancelled.' => "Payment successfully cancelled.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//*[@id='overrides']/div/a",
        'Noted Transaction Identification' => ''
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