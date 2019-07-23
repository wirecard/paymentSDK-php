<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCreateUIPaymentAction extends CreditCardCreateUiBase
{
    // include url of current page
    public $URL = '/CreditCard/createUi.php?paymentAction=';

    public $elements = array(
        'First name' => "//*[@id='first_name']",
        'Last name' => "//*[@id='last_name']",
        'Card number' => "//*[@id='account_number']",
        'CVV' => "//*[@id='card_security_code']",
        'Valid until month' => "//*[@id='expiration_month_list']",
        'Valid until year' => "//*[@id='expiration_year_list']",
        'Amount' => "//*[@id='amount']",
        'Currency' => "//*[@id='currency']",
        'Save' => "//*[@class='btn btn-primary']",
        'Credit Card payment form' => "//*[@id='payment-form']"
    );

    /**
     * Method getURL
     * @param string $scenarioName
     * @return string
     */
    public function getURL($scenarioName)
    {
        $action = (strpos($scenarioName, 'Authorization') ? 'authorization' : 'purchase');
        $amount = (strpos($scenarioName, 'Non3D') ? '25' : '70');
        return $this->URL . $action . '&amount=' . $amount;
    }
}
