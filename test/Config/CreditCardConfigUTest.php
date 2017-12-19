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
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Config;

use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;

class CreditCardConfigUTest extends \PHPUnit_Framework_TestCase
{
    const MAID = 'maiiiidddd';
    const SECRET = 'mytopsecretsecret';
    const THREE_D_MAID = 'hllgjgj';
    const THREE_D_SECRET = 'hfjegrh';
    /**
     * @var CreditCardConfig
     */
    private $config;

    /**
     * @var Amount
     */
    private $amount;

    public function setUp()
    {
        $this->config = new CreditCardConfig(self::MAID, self::SECRET);
        $this->amount = new Amount(10.0, 'EUR');
    }

    public function testGetPaymentMethodName()
    {
        $this->assertEquals(CreditCardTransaction::NAME, $this->config->getPaymentMethodName());
    }

    public function testGetMerchantAccountId()
    {
        $this->assertEquals(self::MAID, $this->config->getMerchantAccountId());
    }

    public function testGetSecret()
    {
        $this->assertEquals(self::SECRET, $this->config->getSecret());
    }

    public function testAddSslMaxLimit()
    {
        $this->config->addSslMaxLimit($this->amount);

        $this->assertAttributeEquals(
            [$this->amount->getCurrency() => $this->amount->getValue()],
            'sslMaxLimits',
            $this->config
        );
    }

    public function testGetSslMaxLimits()
    {
        $this->config->addSslMaxLimit($this->amount);
        $this->assertEquals(
            $this->amount->getValue(),
            $this->config->getSslMaxLimit($this->amount->getCurrency())
        );
    }

    public function testAddThreeDMinLimit()
    {
        $this->config->addThreeDMinLimit($this->amount);

        $this->assertAttributeEquals(
            [$this->amount->getCurrency() => $this->amount->getValue()],
            'threeDMinLimits',
            $this->config
        );
    }

    public function testGetThreeDMinLimits()
    {
        $this->config->addThreeDMinLimit($this->amount);
        $this->assertEquals(
            $this->amount->getValue(),
            $this->config->getThreeDMinLimit($this->amount->getCurrency())
        );
    }

    public function testSetThreeDCredentials()
    {
        $this->config->setThreeDCredentials(self::THREE_D_MAID, self::THREE_D_SECRET);

        $this->assertAttributeEquals(
            self::THREE_D_MAID,
            'threeDMerchantAccountId',
            $this->config
        );
        $this->assertAttributeEquals(self::THREE_D_SECRET, 'threeDSecret', $this->config);
    }

    public function testGetThreeDMerchantAccountId()
    {
        $this->config->setThreeDCredentials(self::THREE_D_MAID, self::THREE_D_SECRET);

        $this->assertEquals(self::THREE_D_MAID, $this->config->getThreeDMerchantAccountId());
    }

    public function testGetThreeDSecret()
    {
        $this->config->setThreeDCredentials(self::THREE_D_MAID, self::THREE_D_SECRET);

        $this->assertEquals(self::THREE_D_SECRET, $this->config->getThreeDSecret());
    }
}
