<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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
