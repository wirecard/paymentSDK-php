<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Config;

use Monolog\Logger;
use org\bovigo\vfs\vfsStream;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;

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

    public function testSetPublicKey()
    {
        $expected = 'test';
        $this->config->setPublicKey($expected);

        $this->assertAttributeEquals($expected, 'publicKey', $this->config);
    }

    public function testGetPublicKey()
    {
        $expected = 'test';
        $this->config->setPublicKey($expected);
        $this->assertEquals($expected, $this->config->getPublicKey());
    }

    public function testGetStraightforwardCase()
    {
        $payPalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, 'mid', 'key');
        $config = $this->config->add($payPalConfig);

        $this->assertEquals($payPalConfig, $config->get(PayPalTransaction::NAME));
    }

    public function testGetFallback()
    {
        $ratepayInvoiceConfig = new PaymentMethodConfig(RatepayInvoiceTransaction::NAME, 'mid', 'key');
        $this->config->add($ratepayInvoiceConfig);
        $ratepayInstallConfig = new PaymentMethodConfig(RatepayInstallmentTransaction::NAME, 'mid', 'key');
        $this->config->add($ratepayInstallConfig);

        $this->assertEquals($ratepayInvoiceConfig, $this->config->get(RatepayInvoiceTransaction::PAYMENT_NAME));
        $this->assertEquals($ratepayInstallConfig, $this->config->get(RatepayInstallmentTransaction::PAYMENT_NAME));
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

        $this->config->get(SepaDirectDebitTransaction::NAME);
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
        $versionFile = __DIR__ . '/../../VERSION';
        $version = '';
        if (file_exists($versionFile)) {
            $version = file_get_contents($versionFile, null, null, 0, 10);
        }

        $expected = array(
            'shop-system-name' => 'paymentSDK-php',
            'shop-system-version' => trim($version, " \t\n\r\0\x0B"),
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

    public function testGetShopHeaderSetShopAndOnlyShopName()
    {
        $expected = array(
            'shop-system-name' => 'testshop',
            'shop-system-version' => '1.1',
        );
        $this->config->setShopInfo($expected['shop-system-name'], $expected['shop-system-version']);
        $this->config->setPluginInfo('pluginName', '');

        $this->assertEquals(array('headers' => $expected), $this->config->getShopHeader());
    }

    /**
     * @since 3.7.1
     */
    public function testGetNvpShopInformationSetPlugin()
    {
        $versionFile = __DIR__ . '/../../VERSION';
        $version = '';
        if (file_exists($versionFile)) {
            $version = file_get_contents($versionFile, null, null, 0, 10);
        }

        $expected = array(
            'shop_system_name' => 'paymentSDK-php',
            'shop_system_version' => trim($version, " \t\n\r\0\x0B"),
            'plugin_name' => 'plugin',
            'plugin_version' => '1.0'
        );
        $this->config->setPluginInfo($expected['plugin_name'], $expected['plugin_version']);

        $this->assertEquals($expected, $this->config->getNvpShopInformation());
    }

    /**
     * @since 3.7.1
     */
    public function testGetNvpShopInformationSetShop()
    {
        $expected = array('shop_system_name' => 'testshop', 'shop_system_version' => '1.1');
        $this->config->setShopInfo($expected['shop_system_name'], $expected['shop_system_version']);

        $this->assertEquals($expected, $this->config->getNvpShopInformation());
    }

    /**
     * @since 3.7.1
     */
    public function testGetNvpShopInformationSetShopAndOnlyShopName()
    {
        $expected = array(
            'shop_system_name' => 'testshop',
            'shop_system_version' => '1.1',
        );
        $this->config->setShopInfo($expected['shop_system_name'], $expected['shop_system_version']);
        $this->config->setPluginInfo('pluginName', '');

        $this->assertEquals($expected, $this->config->getNvpShopInformation());
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

    public function testGetVersionFromExistingFileWithVersionLengthTen()
    {
        $getHelper = function ($file) {
            return $this->getVersionFromFile($file);
        };
        $bound = $getHelper->bindTo($this->config, $this->config);

        $root = vfsStream::setup();
        $file = vfsStream::newFile('version-test.txt')
            ->withContent('1.0.0.0.12.123')
            ->at($root);

        $this->assertEquals('1.0.0.0.12', $bound($file->url()));
    }


    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testInvalidCreditCardConfig()
    {
        new PaymentMethodConfig(PayPalTransaction::NAME, 'maid');
    }
}
