<?php

/* Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;

class PayPalLogIn extends Base
{
    //include url of current page
    public $URL = 'PayPal/reserve.php';

    //page specific text that can be found in the URL
    public $pageSpecific = 'express-checkout';

    //page elements
    public $elements = array(
        'Email' => "//*[@id='email']",
        'Password' => "//*[@id='password']",
        'Next' => "//*[@id='btnNext']",
        'Log In' => "//*[@id='btnLogin']"
    );

    /**
     * Method performPaypalLogin
     *
     * @since 3.7.2
     */
    public function performPaypalLogin()
    {
        $I = $this->tester;
        $data_field_values = $I->getDataFromDataFile('tests/_data/payPalData.json');

        try {
            $I->waitForElementVisible($this->getElement('Email'), 15);
            $I->fillField($this->getElement('Email'), $data_field_values->user_name);
            try {
                $I->waitForElementVisible($this->getElement('Password'));
                $I->fillField($this->getElement('Password'), $data_field_values->password);
            } catch (TimeOutException $e) {
                $I->waitForElementVisible($this->getElement('Next'), 30);
                $I->click($this->getElement('Next'));
                $I->waitForElementVisible($this->getElement('Password'));
                $I->fillField($this->getElement('Password'), $data_field_values->password);
            }
            $I->waitForElementVisible($this->getElement('Log In'), 30);
            $I->click($this->getElement('Log In'));
        } catch (NoSuchElementException $e) {
            $I->seeInCurrentUrl($this->getPageSpecific());
        }
    }
}
