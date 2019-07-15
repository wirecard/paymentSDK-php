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
use ReflectionClass;
use Wirecard\PaymentSdk\Entity\BankAccount;
use Wirecard\PaymentSdk\Transaction\GiropayTransaction;

class GiropayTransactionUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var GiropayTransaction
     */
    private $tx;

    public function setUp()
    {
        $this->tx = new GiropayTransaction();
        $this->tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
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

    public function testSetBankData()
    {
        $bankAccountMock = $this->createMock(BankAccount::class);

        $bankAccountMock->method('mappedProperties')
            ->willReturn(new BankAccount());

        $return = $this->tx->setBankAccount($bankAccountMock);

        $this->assertInstanceOf(GiropayTransaction::class, $return);
    }

    public static function callMethod($obj, $name, $args = [])
    {
        $class = new ReflectionClass($obj);
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
