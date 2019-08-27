<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCreateUiBase extends Base
{
    //include url of current page
    public $URL = '/CreditCard/';

    //page specific text that can be found in the URL
    public $pageSpecific = 'createUi';

    //wirecard seamless frame name
    public $wirecardFrame = '';

    /**
     * Method switchFrame
     * @param boolean $wpp2
     */
    public function switchFrame($wpp2 = false)
    {
        $wirecardFrame = ($wpp2 ? "wirecard-integrated-payment-page-frame" : "wirecard-seamless-frame");
        $I = $this->tester;
        $this->wirecardFrame = $wirecardFrame;
        $I->executeJS('jQuery(".' . $this->wirecardFrame . '").attr("name", "' . $this->wirecardFrame . '")');
        $I->switchToIFrame("$this->wirecardFrame");
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
        $env = $gateway = $I->getGateway();
        $I->waitForElementVisible($this->getElement("Card number"), 30);

        if ('sg_gateway' == $env) {
            $I->waitForElementVisible($this->getElement("First name"), 30);
            $I->fillField($this->getElement("First name"), $data_field_values->first_name);
        }
        if ('sg_secure_gateway' != $env) {
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

    /**
     * Method getURL
     * @param string $scenarioName
     * @return string
     */
    public function getURL($scenarioName)
    {
        $url = $this->URL;
        if (strpos($scenarioName, 'AndPostprocessing')) {
            $action = (strpos($scenarioName, 'Authorization') ? 'authorization' : 'purchase');
            $amount = (strpos($scenarioName, 'Non3D') ? '25' : '70');
            $url = $url . $action . '&amount=' . $amount;
        }
        return $url;
    }
}
