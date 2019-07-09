<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCreateUiBase extends Base
{
    // include url of current page
    public $URL = '/CreditCard/createUiWppV2NonThreeD.php';

    public $page_specific = 'createUi';

    public $wirecard_frame = 'wirecard-integrated-payment-page-frame';

    public $elements = array(
        'First name' => "//*[@id='pp-cc-first-name']",
        'Last name' => "//*[@id='pp-cc-last-name']",
        'Card number' => "//*[@id='pp-cc-account-number']",
        'CVV' => "//*[@id='pp-cc-cvv']",
        'Valid until month / year' => "//*[@id='pp-cc-expiration-date']",
        'Save' => "//*[@class='btn btn-primary']",
        'Credit Card payment form' => "//*[@id='payment-form']"
    );

    /**
     * Method switchFrame
     * @param boolean $wpp2
     */
    public function switchFrame($wpp2 = false)
    {

        $wirecard_frame = ($wpp2 ? "wirecard-integrated-payment-page-frame" : "wirecard-seamless-frame");
        $I = $this->tester;
        $this->wirecard_frame = $wirecard_frame;
        $I->executeJS('jQuery(".' . $this->wirecard_frame . '").attr("name", "' . $this->wirecard_frame . '")');
        $I->switchToIFrame("$this->wirecard_frame");
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
     * @param null $type
     * @throws
     */
    public function fillCreditCardFields($cardData, $type = null)
    {
        $I = $this->tester;
        $data_field_values = $I->getCardDataFromDataFile($cardData);
        $env = getenv('GATEWAY');
        $I->waitForElementVisible($this->getElement("Card number"));

        if ('TEST-SG' == $env) {
            $I->waitForElementVisible($this->getElement("First name"));
            $I->fillField($this->getElement("First name"), $data_field_values->first_name);
        }
        if ('SECURE-TEST-SG' != $env) {
            $I->fillField($this->getElement("Last name"), $data_field_values->last_name);
        }
        $I->fillField($this->getElement("Card number"), $data_field_values->card_number);
        $I->fillField($this->getElement("CVV"), $data_field_values->cvv);

        if ($type == 'WPP') {
            $I->fillfield(
                $this->getElement("Valid until month / year"),
                $data_field_values->valid_until_month
                .substr($data_field_values->valid_until_year, -2)
            );
        } else {
            $I->selectOption($this->getElement("Valid until month"), $data_field_values->valid_until_month);
            $I->selectOption($this->getElement("Valid until year"), $data_field_values->valid_until_year);
        }

        $I->switchToIFrame();
    }
}
