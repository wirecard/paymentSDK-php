<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Config;

use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Config\MaestroConfig;
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
        $returned_config = $this->config->addSslMaxLimit($this->amount);

        $this->assertAttributeEquals(
            [$this->amount->getCurrency() => $this->amount->getValue()],
            'sslMaxLimits',
            $returned_config
        );
    }

    public function testAddNonThreeDMaxLimit()
    {
        $this->config->addNonThreeDMaxLimit($this->amount);

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

    public function testGetNonThreeDMaxLimits()
    {
        $this->config->addNonThreeDMaxLimit($this->amount);
        $this->assertEquals(
            $this->amount->getValue(),
            $this->config->getNonThreeDMaxLimit($this->amount->getCurrency())
        );
    }

    public function testAddThreeDMinLimit()
    {
        $returned_config = $this->config->addThreeDMinLimit($this->amount);

        $this->assertAttributeEquals(
            [$this->amount->getCurrency() => $this->amount->getValue()],
            'threeDMinLimits',
            $returned_config
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
        $returned_config = $this->config->setThreeDCredentials(self::THREE_D_MAID, self::THREE_D_SECRET);

        $this->assertAttributeEquals(
            self::THREE_D_MAID,
            'threeDMerchantAccountId',
            $this->config
        );
        $this->assertAttributeEquals(self::THREE_D_SECRET, 'threeDSecret', $returned_config);
    }

    public function testSetOnlyMaid()
    {
        $config = new CreditCardConfig(self::MAID, null);
        $this->assertTrue(is_null($config->getMerchantAccountId()));
    }

    public function testSetOnlySecret()
    {
        $config = new CreditCardConfig(null, self::SECRET);
        $this->assertTrue(is_null($config->getSecret()));
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

    public function testSetSSLCredentials()
    {
        $returned_config = $this->config->setSSLCredentials('maid', 'secret');

        $this->assertEquals(
            ['maid', 'secret'],
            [$returned_config->getMerchantAccountId(), $returned_config->getSecret()]
        );
    }

    public function testSetNonThreeDCredentials()
    {
        $returned_config = $this->config->setNonThreeDCredentials('maid', 'secret');

        $this->assertEquals(
            ['maid', 'secret'],
            [$returned_config->getMerchantAccountId(), $returned_config->getSecret()]
        );
    }

    public function testNewCreditCardConfig()
    {
        $creditCardConfig = new MaestroConfig('maid', 'secret');

        $this->assertEquals('maid', $creditCardConfig->getMerchantAccountId());
    }
}
