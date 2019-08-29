<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Transaction;

use Generator;
use PHPUnit_Framework_TestCase;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PayByBankAppTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class PayByBankAppTransactionUTest
 * @package WirecardTest\PaymentSdk\Transaction
 * @coversDefaultClass \Wirecard\PaymentSdk\Transaction\PayByBankAppTransaction
 */
class PayByBankAppTransactionUTest extends PHPUnit_Framework_TestCase
{
    /** @var PayByBankAppTransaction */
    private $object;

    public function setUp()
    {
        $this->object = new PayByBankAppTransaction();
        $this->object->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
    }

    /**
     * @group unit
     * @small
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->object->setOperation('non-existing');
        $this->object->mappedProperties();
    }

    /**
     * @group unit
     * @small
     * @covers ::mappedSpecificProperties
     * @throws \ReflectionException
     */
    public function testMappedSpecificProperties()
    {
        $class = new \ReflectionClass($this->object);
        $method = $class->getMethod('mappedSpecificProperties');
        $method->setAccessible(true);
        $result = $method->invoke($this->object);
        $this->assertTrue(is_array($result));
        $this->assertEmpty($result);
    }

    /**
     * @group unit
     * @small
     * @covers ::retrieveTransactionTypeForPay
     * @throws \ReflectionException
     */
    public function testGetRetrieveTransactionTypePay()
    {
        $class = new \ReflectionClass($this->object);
        $method = $class->getMethod('retrieveTransactionTypeForPay');
        $method->setAccessible(true);
        $this->assertEquals(Transaction::TYPE_DEBIT, $method->invoke($this->object));
    }

    /**
     * @group unit
     * @small
     * @covers ::retrieveTransactionTypeForPay
     * @param string $parentTransactionType
     * @param string $expected
     * @dataProvider payDataProvider
     */
    public function testGetRetrieveTransactionTypePayMapping($parentTransactionType, $expected)
    {
        $this->object->setParentTransactionType($parentTransactionType);
        $this->object->setOperation(Operation::PAY);
        $data = $this->object->mappedProperties();

        $this->assertEquals($expected, $data['transaction-type']);
    }


    /**
     * @return Generator
     */
    public function payDataProvider()
    {
        yield [null, Transaction::TYPE_DEBIT];
        yield [Transaction::TYPE_DEBIT, Transaction::TYPE_DEBIT];
    }

    /**
     * @group unit
     * @small
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     * @covers ::retrieveTransactionTypeForCancel
     */
    public function testGetRetrieveTransactionTypeCancelWithoutParentTransaction()
    {
        $this->object->setOperation(Operation::CANCEL);
        $this->object->mappedProperties();
    }

    /**
     * @group unit
     * @small
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     * @covers ::retrieveTransactionTypeForCancel
     */
    public function testGetRetrieveTransactionTypeCancelWithInvalidParentTransactionType()
    {
        $this->object->setOperation(Operation::CANCEL);
        $this->object->setParentTransactionId('1');
        $this->object->mappedProperties();
    }

    /**
     * @return Generator
     */
    public function cancelDataProvider()
    {
        yield [Transaction::TYPE_DEBIT, Transaction::TYPE_REFUND_REQUEST];
    }

    /**
     * @group unit
     * @small
     * @param string $parentTransactionType
     * @param string $expected
     * @dataProvider cancelDataProvider
     * @covers ::retrieveTransactionTypeForCancel
     */
    public function testGetRetrieveTransactionTypeCancel($parentTransactionType, $expected)
    {
        $this->object->setParentTransactionId('1');
        $this->object->setParentTransactionType($parentTransactionType);
        $this->object->setOperation(Operation::CANCEL);

        $data = $this->object->mappedProperties();

        $this->assertEquals($expected, $data['transaction-type']);
    }

    /**
     * @return Generator
     */
    public function dataProviderGetEndpoint()
    {
        yield [Operation::CANCEL, Transaction::ENDPOINT_PAYMENTS];
        yield [Operation::REFUND, Transaction::ENDPOINT_PAYMENTS];
        yield [Operation::PAY, Transaction::ENDPOINT_PAYMENT_METHODS];
        yield ["", Transaction::ENDPOINT_PAYMENT_METHODS];
        yield [null, Transaction::ENDPOINT_PAYMENT_METHODS];
    }

    /**
     * @group unit
     * @small
     * @covers ::getEndpoint
     * @param $operation
     * @param $expectedEndpoint
     * @dataProvider dataProviderGetEndpoint
     */
    public function testGetEndpoint($operation, $expectedEndpoint)
    {
        $this->object->setOperation($operation);
        $endpoint = $this->object->getEndpoint();
        $this->assertNotEmpty($endpoint);
        $this->assertTrue(is_string($endpoint));
        $this->assertEquals($expectedEndpoint, $endpoint);
    }
}
