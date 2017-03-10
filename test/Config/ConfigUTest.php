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

namespace WirecardTest\PaymentSdk\Config;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;

class ConfigUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $instance;

    public function setUp()
    {
        $this->instance = new Config(
            'http://www.example.com',
            'httpUser',
            'httpPassword'
        );
    }

    public function testGetBaseUrl()
    {
        $this->assertEquals('http://www.example.com', $this->instance->getBaseUrl());
    }

    public function testGetHttpUser()
    {
        $this->assertEquals('httpUser', $this->instance->getHttpUser());
    }

    public function testGetHttpPassword()
    {
        $this->assertEquals('httpPassword', $this->instance->getHttpPassword());
    }

    public function testGetDefaultCurrency()
    {
        $this->assertEquals('EUR', $this->instance->getDefaultCurrency());
    }

    public function testGetStraightforwardCase()
    {
        $payPalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, 'mid', 'key');
        $this->instance->add($payPalConfig);

        $this->assertEquals($payPalConfig, $this->instance->get(PayPalTransaction::NAME));
    }

    public function testGetFallback()
    {
        $sepaConfig = new PaymentMethodConfig(SepaTransaction::NAME, 'mid', 'key');
        $this->instance->add($sepaConfig);

        $this->assertEquals($sepaConfig, $this->instance->get(SepaTransaction::DIRECT_DEBIT));
        $this->assertEquals($sepaConfig, $this->instance->get(SepaTransaction::CREDIT_TRANSFER));
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException
     */
    public function testGetUnknownPaymentMethod()
    {
        $payPalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, 'mid', 'key');
        $this->instance->add($payPalConfig);

        $this->instance->get('unknown_payment_method');
    }

    public function testSetDefaultCurrency()
    {
        $this->instance = new Config(
            'http://www.example.com',
            'httpUser',
            'httpPassword',
            'USD'
        );

        $this->assertEquals('USD', $this->instance->getDefaultCurrency());
    }
}
