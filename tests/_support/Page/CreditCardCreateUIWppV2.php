<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCreateUIWppV2 extends Base
{
    // include url of current page
    public $URL = '/CreditCard/createUiWppV2.php';

    public $page_specific = 'createUi';

    public $elements = array(
        'First name' => "//*[@id='pp-cc-first-name']",
        'Last name' => "//*[@id='pp-cc-last-name']",
        'Card number' => "//*[@id='pp-cc-account-number']",
        'CVV' => "//*[@id='pp-cc-cvv']",
        'Valid until month / year' => "//*[@id='pp-cc-expiration-date']",
//        'Address 1' => "//*[@id='pp-cc-address1']",
//        'Address 2' => "//*[@id='pp-cc-address2']",
//        'City' => "//*[@id='pp-cc-city']",
//        'State' => "//*[@id='pp-cc-state']",
//        'Postal Code' => "//*[@id='pp-cc-postal-code']",
//        'Country' => "//*[@id='pp-cc-country']",
//        'Mail' => "//*[@id='pp-cc-email']",
//        'Phone' => "//*[@id='pp-cc-phone']",

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
     * @param string $cardData
     */
    public function fillCreditCardFields($cardData)
    {
        $I = $this->tester;
        $data_field_values = $I->getCardDataFromDataFile($cardData);
        $env = getenv('GATEWAY');
        $I->waitForElementVisible($this->getElement("Card number"));

        if ('TEST-SG' == $env) {
            $I->waitForElementVisible($this->getElement("First name"));
            $I->fillField($this->getElement("First name"), $data_field_values->first_name);
        }

        $I->fillField($this->getElement("Last name"), $data_field_values->last_name);
        $I->fillField($this->getElement("Card number"), $data_field_values->card_number);
        $I->fillField($this->getElement("CVV"), $data_field_values->cvv);
        $I->fillfield($this->getElement("Valid until month / year"), $data_field_values->valid_until_month);
        $I->switchToIFrame();
    }
}
