<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardPayBasedOnReserve extends Base
{
    // include url of current page
    public $URL = '/CreditCard/pay-based-on-reserve.php';

    public $elements = array(
        'Reserved transaction ID' => "//*[@id='parentTransactionId']",
        'Amount' => "//*[@id='amount']",
        'Payment successfully completed.' => "Payment successfully completed.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a",
        'Noted Transaction Identification' => '',
        'Pay' => "//*[@class='btn btn-primary']",
    );

    /**
     * Method prepareDataForField
     *
     * @param string $fieldValue
     * @param string $valueToKeepBetweenSteps
     * @return string
     */
    public function prepareDataForField($fieldValue, $valueToKeepBetweenSteps)
    {
        if (strpos($fieldValue, "Noted") !== false) {
            return $valueToKeepBetweenSteps;
        } else {
            return $fieldValue;
        }
    }
}
