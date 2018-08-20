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

use ReflectionClass;
use Wirecard\PaymentSdk\Entity\BankAccount;
use Wirecard\PaymentSdk\Transaction\GiropayTransaction;

class GiropayTransactionUTest extends \PHPUnit_Framework_TestCase
{
    private $tx;

    public function setUp()
    {
        $this->tx = new GiropayTransaction();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }

    public function testSetOrderDetail()
    {
        $orderDetail = $this->tx->setOrderDetail('order-detail');

        $this->assertInstanceOf(GiropayTransaction::class, $orderDetail);
    }

    public function testsetBankData()
    {
        $bankAccountMock = $this->createMock(BankAccount::class);

        $bankAccountMock->method('mappedProperties')
            ->willReturn(new BankAccount());

        $return = $this->tx->setBankAccount($bankAccountMock);

        $this->assertInstanceOf(GiropayTransaction::class, $return);
    }

    public static function callMethod($obj, $name, $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }


    public function testMapSpecificProperties()
    {

        $bankAccount = new BankAccount();
        $bankAccount->setBic("BICTEST");

        $this->tx->setOrderDetail('DETAILTEST');
        $this->tx->setBankAccount($bankAccount);

        $data = $this->callMethod(
            $this->tx,
            'mappedSpecificProperties',
            []
        );


        $this->assertEquals(true, count($data) > 1);
    }

    public function testShouldReturnTransactionTypeForPay()
    {

        $returnType = $this->callMethod($this->tx, 'retrieveTransactionTypeForPay');

        $this->assertEquals('debit', $returnType);
    }

    public function testShouldReturnEndPointParent()
    {

        $reflectionClass = new ReflectionClass($this->tx);

        $property = $reflectionClass->getProperty('parentTransactionId');
        $property->setAccessible(true);
        $property->setValue($this->tx, 'test-with-parent-id');

        $method = $reflectionClass->getMethod('getEndpoint');
        $method->setAccessible(true);

        $newMethod = $method->invokeArgs($this->tx, []);

        $this->assertEquals($newMethod, '/engine/rest/payments/');
    }

    public function testShouldReturnEndPointNotParent()
    {
        $returnType = $this->callMethod($this->tx, 'getEndpoint');

        $this->assertEquals($returnType, '/engine/rest/paymentmethods/');
    }
}
