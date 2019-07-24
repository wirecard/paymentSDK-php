<?php

/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Page;

use Facebook\WebDriver\Exception\TimeOutException;

class PayPalLogIn extends Base
{

    /**
     * @var string
     * @since 1.4.4
     */
    public $URL = 'PayPal/reserve.php';

    /**
     * @var array
     * @since 1.4.4
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
     * @since   1.4.4
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
