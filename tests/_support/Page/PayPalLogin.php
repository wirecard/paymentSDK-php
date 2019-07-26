<?php

/* Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

use Facebook\WebDriver\Exception\TimeOutException;

class PayPalLogIn extends Base
{

    /**
     * @var string
     * @since 3.7.2
     */
    public $URL = 'PayPal/reserve.php';

    /**
     * @var array
     * @since 3.7.2
     */
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
        $I->waitForElementVisible($this->getElement('Email'));
        $I->fillField($this->getElement('Email'), $data_field_values->user_name);
        try {
            $I->waitForElementVisible($this->getElement('Password'));
        } catch (TimeOutException $e) {
            $I->waitForElementVisible($this->getElement('Next'));
            $I->click($this->getElement('Next'));
        }
        $I->waitForElementVisible($this->getElement('Password'));
        $I->fillField($this->getElement('Password'), $data_field_values->password);
        $I->waitForElementVisible($this->getElement('Log In'));
        $I->click($this->getElement('Log In'));
    }
}
