<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCreateUIPurchase extends CreditCardCreateUiBase
{
    // include url of current page
    public $URL = '/CreditCard/createUi.php?paymentAction=purchase&amount=70';

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

    public $wirecard_frame = "wirecard-seamless-frame";

    /**
     * Method switchFrame
     */
    public function switchFrame()
    {
        $I = $this->tester;
        // Switch to Credit Card UI frame
        $wirecard_frame = "wirecard-seamless-frame";
        $I->executeJS('jQuery(".' . $this->wirecard_frame . '").attr("name", "' . $this->wirecard_frame . '")');
        $I->switchToIFrame("$this->wirecard_frame");
    }

    /**
     * Method prepareClick
     */
    public function prepareClick()
    {
        parent::prepareClick();
    }
    /**
     * Method Method prepareDataForField
     * @param string $cardData
     * @param null $type
     */
    public function fillCreditCardFields($cardData, $type = null)
    {
        parent::fillCreditCardFields($cardData);
    }
}
