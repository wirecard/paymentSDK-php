<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Redirect;

class RedirectUTest extends \PHPUnit_Framework_TestCase
{
    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';
    const REDIRECT_URL = 'http://www.example.com/redirect';
    /**
     * @var Redirect
     */
    private $redirect;

    public function setUp()
    {
        $this->redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL, self::REDIRECT_URL);
    }

    public function testGetSuccessUrl()
    {
        $this->assertEquals(self::SUCCESS_URL, $this->redirect->getSuccessUrl());
    }

    public function testGetCancelUrl()
    {
        $this->assertEquals(self::CANCEL_URL, $this->redirect->getCancelUrl());
    }

    public function testGetFailureUrl()
    {
        $this->assertEquals(self::REDIRECT_URL, $this->redirect->getFailureUrl());
    }
}
