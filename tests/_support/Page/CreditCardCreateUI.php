<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCreateUI extends Base
{
    // include url of current page
    public $URL = '/CreditCard/createUi_tokenize.php';

    public $elements = array(
        'Last name' => "//*[@id='last_name']",
        'Card number' => "//*[@id='account_number']",
        'CVV' => "//*[@id='card_security_code']",
        'Valid until month' => "//*[@id='expiration_month_list']",
        'Valid until year' => "//*[@id='expiration_year_list']",
        'Save' => "//*[@class='btn btn-primary']",
        'Credit Card payment form' => "//*[@id='payment-form']"
    );

    /**
     * Method switchFrame
     */
    public function switchFrame()
    {
        $I = $this->tester;
        // Switch to Credit Card UI frame
        $wirecard_frame = "wirecard-seamless-frame";
        $I->executeJS('jQuery(".' . $wirecard_frame . '").attr("name", "' . $wirecard_frame . '")');
        $I->switchToIFrame("$wirecard_frame");
    }

    /**
     * Method prepareClick
     */
    public function prepareClick()
    {
        $I = $this->tester;
        $I->switchToIFrame();
    }

}
