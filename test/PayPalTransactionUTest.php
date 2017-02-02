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

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Money;
use Wirecard\PaymentSdk\PayPalTransaction;
use Wirecard\PaymentSdk\Redirect;

class PayPalTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const NOTIFICATION_URL = 'http://www.example.com';
    /**
     * @var Money
     */
    private $amount;

    /**
     * @var PayPalTransaction
     */
    private $payPalTransaction;

    /**
     * @var Redirect
     */
    private $redirect;

    public function setUp()
    {
        $this->amount = new Money(42.21, 'EUR');
    }

    public function testConstructorWithRedirect()
    {
        $this->redirect = new Redirect('http://www.example.com/success', 'http://www.example.com/cancel');
        $this->payPalTransaction = new PayPalTransaction($this->amount, self::NOTIFICATION_URL, $this->redirect);

        $this->assertEquals($this->amount, $this->payPalTransaction->getAmount());
        $this->assertEquals(self::NOTIFICATION_URL, $this->payPalTransaction->getNotificationUrl());
        $this->assertEquals($this->redirect, $this->payPalTransaction->getRedirect());
    }
}
