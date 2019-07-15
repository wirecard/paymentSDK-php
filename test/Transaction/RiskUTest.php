<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Transaction;

use PHPUnit_Framework_TestCase;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Risk;
use Wirecard\PaymentSdk\Transaction\Transaction;

class RiskUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Transaction
     */
    private $tx;

    public function setUp()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $txMock
         */
        $this->tx = $this->getMockBuilder(Risk::class)->getMockForAbstractClass();
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
    }

    public function testMappingForConsumerId()
    {
        $this->tx->setConsumerId('b4');
        $mapped = $this->tx->mappedProperties();

        $this->assertEquals('b4', $mapped['consumer-id']);
    }

    public function testSetAccountHolder()
    {
        $accountholder = new AccountHolder();
        $accountholder->setLastName('Doe');
        $this->tx->setAccountHolder($accountholder);
        $mapped = $this->tx->mappedProperties();
        $this->assertEquals('Doe', $mapped['account-holder']['last-name']);
    }

    public function testMappingForIpAddress()
    {
        $this->tx->setIpAddress('100.000.2');
        $mapped = $this->tx->mappedProperties();

        $this->assertEquals('100.000.2', $mapped['ip-address']);
    }

    public function testSetShipping()
    {
        $accountholder = new AccountHolder();
        $accountholder->setLastName('Doe');
        $this->tx->setShipping($accountholder);
        $mapped = $this->tx->mappedProperties();
        $this->assertEquals('Doe', $mapped['shipping']['last-name']);
    }

    public function testMappedPropertiesSetsOrderItems()
    {
        /**
         * @var Redirect $redirect
         */
        $this->tx->setBasket(new Basket());
        $data = $this->tx->mappedProperties();

        $this->assertArrayHasKey('order-items', $data);
    }

    public function testSetDevice()
    {
        $fingerprint = "ABCD1234EFG";
        $device = new Device();
        $device->setFingerprint($fingerprint);

        $this->tx->setDevice($device);
        $data = $this->tx->mappedProperties();

        $this->assertEquals($device->mappedProperties(), $data['device']);
    }

    public function testMappingForOrderNumber()
    {
        $this->tx->setOrderNumber('001');
        $mapped = $this->tx->mappedProperties();

        $this->assertEquals('001', $mapped['order-number']);
    }

    public function testMappingForDescriptor()
    {
        $this->tx->setDescriptor('Testshop');
        $mapped = $this->tx->mappedProperties();

        $this->assertEquals('Testshop', $mapped['descriptor']);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedEncodingException
     */
    public function testDescriptorException()
    {
        $descriptor = iconv('UTF-8', 'ISO-8859-1', 'Test üöäü?=(&$§"§$!');
        $this->tx->setDescriptor($descriptor);
    }
}
