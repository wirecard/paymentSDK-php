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
        'First name' => "//*[@id='first_name']",
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
    /**
     * Method Method prepareDataForField
     * @param string $carddata
     */
    public function fillCreditCardFields($carddata){
        $I = $this->tester;
        $data_field_values = $I->getCardDataFromDataFile($carddata);
        $env = getenv('GATEWAY');

        if ($env === 'NOVA' || $env === 'API-WDCEE-TEST' || $env === 'API-TEST') {
            $I->fillField($this->getElement("Last name"), $data_field_values->last_name);
            $I->fillField($this->getElement("Card number"), $data_field_values->card_number);
            $I->fillField($this->getElement("CVV"), $data_field_values->cvv);
            $I->selectOption($this->getElement("Valid until month"), $data_field_values->valid_until_month);
            $I->selectOption($this->getElement("Valid until year"), $data_field_values->valid_until_year);

        } else if ($env === 'TEST-SG') {
            $I->fillField($this->getElement("First name"), $data_field_values->first_name);
            $I->fillField($this->getElement("Last name"), $data_field_values->last_name);
            $I->fillField($this->getElement("Card number"), $data_field_values->card_number);
            $I->fillField($this->getElement("CVV"), $data_field_values->cvv);
            $I->selectOption($this->getElement("Valid until month"), $data_field_values->valid_until_month);
            $I->selectOption($this->getElement("Valid until year"), $data_field_values->valid_until_year);

        } else if ($env === 'SECURE-TEST-SG') {
            $I->fillField($this->getElement("Card number"), $data_field_values->card_number);
            $I->fillField($this->getElement("CVV"), $data_field_values->cvv);
            $I->selectOption($this->getElement("Valid until month"), $data_field_values->valid_until_month);
            $I->selectOption($this->getElement("Valid until year"), $data_field_values->valid_until_year);

        }
    }
}
