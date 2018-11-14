<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCancel extends Base
{
    // include url of current page
    public $URL = '/CreditCard/cancel.php';

    public $elements = array(
        'Transaction ID to be refunded' => "//*[@id='parentTransactionId']",
        'Refund' => "//*[@class='btn btn-primary']",
        '"Payment successfully cancelled.' => "Payment successfully cancelled.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//*[@id='overrides']/div/a",
        'Noted Transaction Identification' => ''
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
