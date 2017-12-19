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

namespace WirecardTest\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Risk;
use Wirecard\PaymentSdk\Transaction\Transaction;

class RiskUTest extends \PHPUnit_Framework_TestCase
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
        return $this->tx;
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
}
