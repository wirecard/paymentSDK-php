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

use Monolog\Logger;
use org\bovigo\vfs\vfsStream;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;

/**
 * Class ConfigUTest
 * @package WirecardTest\PaymentSdk\Config
 * @method getVersionFromFile($file)
 */
class ConfigUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    public function setUp()
    {
        $this->config = new Config(
            'http://www.example.com',
            'httpUser',
            'httpPassword'
        );
        $this->config->setLogLevel(Logger::ERROR);
    }

    public function testGetBaseUrl()
    {
        $this->assertEquals('http://www.example.com', $this->config->getBaseUrl());
    }

    public function testGetHttpUser()
    {
        $this->assertEquals('httpUser', $this->config->getHttpUser());
    }

    public function testGetHttpPassword()
    {
        $this->assertEquals('httpPassword', $this->config->getHttpPassword());
    }

    public function testGetDefaultCurrency()
    {
        $this->assertEquals('EUR', $this->config->getDefaultCurrency());
    }

    public function testGetStraightforwardCase()
    {
        $payPalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, 'mid', 'key');
        $this->config->add($payPalConfig);

        $this->assertEquals($payPalConfig, $this->config->get(PayPalTransaction::NAME));
    }

    public function testGetFallback()
    {
        $sepaConfig = new PaymentMethodConfig(SepaTransaction::NAME, 'mid', 'key');
        $this->config->add($sepaConfig);

        $this->assertEquals($sepaConfig, $this->config->get(SepaTransaction::DIRECT_DEBIT));
        $this->assertEquals($sepaConfig, $this->config->get(SepaTransaction::CREDIT_TRANSFER));
    }

    public function testGetUseSpecificIfExistsAndNotFallback()
    {
        $sepaConfig = new PaymentMethodConfig(SepaTransaction::NAME, 'mid', 'key');
        $ddConfig = new PaymentMethodConfig(SepaTransaction::DIRECT_DEBIT, 'dd_mid', 'other_key');
        $this->config->add($sepaConfig);
        $this->config->add($ddConfig);

        $this->assertEquals($ddConfig, $this->config->get(SepaTransaction::DIRECT_DEBIT));
        $this->assertEquals($sepaConfig, $this->config->get(SepaTransaction::CREDIT_TRANSFER));
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException
     */
    public function testGetUnknownPaymentMethod()
    {
        $payPalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, 'mid', 'key');
        $this->config->add($payPalConfig);

        $this->config->get('unknown_payment_method');
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException
     */
    public function testGetUnknownPaymentMethodBecauseFallbackAlsoUnknown()
    {
        $payPalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, 'mid', 'key');
        $this->config->add($payPalConfig);

        $this->config->get(SepaTransaction::DIRECT_DEBIT);
    }

    public function testSetDefaultCurrency()
    {
        $this->config = new Config(
            'http://www.example.com',
            'httpUser',
            'httpPassword',
            'USD'
        );

        $this->assertEquals('USD', $this->config->getDefaultCurrency());
    }

    public function testSetLogLevel()
    {
        $this->config = new Config(
            'http://www.example.com',
            'httpUser',
            'httpPassword',
            'USD'
        );
        $logLevel = 20;
        $this->config->setLogLevel($logLevel);

        $this->assertEquals($this->config->getLogLevel(), $logLevel);
    }

    public function testGetShopHeaderSetPlugin()
    {
        $expected = array(
            'shop-system-name' => 'paymentSDK-php',
            'shop-system-version' => '',
            'plugin-name' => 'plugin',
            'plugin-version' => '1.0'
        );
        $this->config->setPluginInfo($expected['plugin-name'], $expected['plugin-version']);

        $this->assertEquals(array('headers' => $expected), $this->config->getShopHeader());
    }

    public function testGetShopHeaderSetShop()
    {
        $expected = array('shop-system-name' => 'testshop', 'shop-system-version' => '1.1');
        $this->config->setShopInfo($expected['shop-system-name'], $expected['shop-system-version']);

        $this->assertEquals(array('headers' => $expected), $this->config->getShopHeader());
    }

    public function testGetVersionFromNotExistingFile()
    {
        $helper = function ($file) {
            return $this->getVersionFromFile($file);
        };

        $bound = $helper->bindTo($this->config, $this->config);

        $file = 'NonExistentFile';
        $this->assertEquals('', $bound($file));
    }

    public function testGetVersionFromExistingFile()
    {
        $getHelper = function ($file) {
            return $this->getVersionFromFile($file);
        };
        $bound = $getHelper->bindTo($this->config, $this->config);

        $root = vfsStream::setup();
        $file = vfsStream::newFile('version-test.txt')
            ->withContent('1.0.0')
            ->at($root);

        $this->assertEquals('1.0.0', $bound($file->url()));
    }
}
