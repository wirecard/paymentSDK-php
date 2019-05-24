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

use PHPUnit_Framework_TestCase;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

class TransactionUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Transaction
     */
    private $tx;

    /**
     * @param $method
     * @param $transactionType
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockWithoutRetrieveMethod($method, $transactionType)
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $txMock
         */
        $txMock = $this->getMockBuilder(Transaction::class)
            ->setMethods([$method])
            ->getMockForAbstractClass();
        $txMock->expects($this->any())
            ->method($method)
            ->will($this->returnValue($transactionType));
        $txMock->method('mappedSpecificProperties')->willReturn([]);
        return $txMock;
    }

    public function setUp()
    {
        $this->tx = $this->getMockWithoutRetrieveMethod('retrieveTransactionTypeForPay', Transaction::TYPE_DEBIT);
        $this->tx->method('mappedSpecificProperties')->willReturn([]);
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testMappingForConsumerId()
    {
        $this->tx->setConsumerId('b4');
        $this->tx->setOperation(Operation::PAY);
        $mapped = $this->tx->mappedProperties();

        $this->assertEquals('b4', $mapped['consumer-id']);
    }

    public function testMappingForCustomFields()
    {
        $this->tx->setCustomFields(new CustomFieldCollection());
        $this->tx->setOperation(Operation::PAY);
        $mapped = $this->tx->mappedProperties();

        $this->assertArrayHasKey('custom-fields', $mapped);
    }

    public function testSetAccountHolder()
    {
        $accountholder = new AccountHolder();
        $accountholder->setLastName('Doe');
        $this->tx->setAccountHolder($accountholder);
        $this->tx->setOperation(Operation::PAY);
        $mapped = $this->tx->mappedProperties();
        $this->assertEquals('Doe', $mapped['account-holder']['last-name']);
    }

    public function testGetEndpoint()
    {
        $this->assertEquals(Transaction::ENDPOINT_PAYMENT_METHODS, $this->tx->getEndpoint());
    }


    public function operationsProvider()
    {
        return [
            [
                Operation::RESERVE,
                'retrieveTransactionTypeForReserve',
                Transaction::TYPE_AUTHORIZATION,
            ],
            [
                Operation::PAY,
                'retrieveTransactionTypeForPay',
                Transaction::TYPE_DEBIT,
            ],
            [
                Operation::CANCEL,
                'retrieveTransactionTypeForCancel',
                Transaction::TYPE_VOID_AUTHORIZATION,
            ],
            [
                Operation::REFUND,
                'retrieveTransactionTypeForRefund',
                Transaction::TYPE_CAPTURE_AUTHORIZATION,
            ],
            [
                Operation::CREDIT,
                'retrieveTransactionTypeForCredit',
                Transaction::TYPE_CREDIT,
            ],
        ];
    }

    /**
     * @dataProvider operationsProvider
     * @param string $operation
     * @param string $method
     * @param string $transactionType
     */
    public function testRetrieveTransactionTypeCallsFunctions($operation, $method, $transactionType)
    {
        $txMock = $this->getMockWithoutRetrieveMethod($method, $transactionType);
        $txMock->expects($this->atLeastOnce())->method($method);
        /**
         * @var Transaction $tx
         */
        $tx = $txMock;
        $tx->setOperation($operation);
        $tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     * @dataProvider operationsProvider
     * @param string $operation
     * @internal param string $method
     * @internal param string $transactionType
     */
    public function testGetRetrieveTransactionTypeForDefaultThrowsException($operation)
    {
        $tx = $this->getMockForAbstractClass(Transaction::class);
        $tx->method('mappedSpecificProperties')->willReturn([]);
        $tx->setOperation($operation);
        $tx->mappedProperties();
    }

    public function testGetSuccessUrlHasNoRedirect()
    {
        $this->assertEquals(null, $this->tx->getSuccessUrl());
    }

    public function testGetSuccessUrlHasRedirect()
    {
        $successUrl = 'success';
        $redirect = new Redirect($successUrl, null);
        $this->tx->setRedirect($redirect);

        $this->assertEquals($successUrl, $this->tx->getSuccessUrl());
    }

    public function testGetBackendOperationForPay()
    {
        $this->assertNotNull($this->tx->getBackendOperationForPay());
    }

    public function testGetBackendOperationForPayException()
    {
        /**
         * @var Transaction $stub
         */
        $stub = $this->getMockForAbstractClass(Transaction::class);
        $this->assertFalse($stub->getBackendOperationForPay());
    }

    public function testGetBackendOperationForCancel()
    {
        $this->assertNotNull($this->tx->getBackendOperationForPay());
    }

    public function testGetBackendOperationForCancelException()
    {
        /**
         * @var Transaction $stub
         */
        $stub = $this->getMockForAbstractClass(Transaction::class);
        $this->assertFalse($stub->getBackendOperationForCancel());
    }

    public function testGetBackendOperationForRefund()
    {
        $this->assertNotNull($this->tx->getBackendOperationForPay());
    }

    public function testGetBackendOperationForRefundException()
    {
        /**
         * @var Transaction $stub
         */
        $stub = $this->getMockForAbstractClass(Transaction::class);
        $this->assertFalse($stub->getBackendOperationForRefund());
    }

    public function testGetBackendOperationForCredit()
    {
        $this->assertNotNull($this->tx->getBackendOperationForPay());
    }

    public function testGetBackendOperationForCreditException()
    {
        /**
         * @var Transaction $stub
         */
        $stub = $this->getMockForAbstractClass(Transaction::class);
        $this->assertFalse($stub->getBackendOperationForCredit());
    }

    public function testGetters()
    {
        /**
         * @var Transaction $stub
         */
        $stub = $this->getMockForAbstractClass(Transaction::class);
        $this->assertNull($stub->getParentTransactionType());
        $this->assertNull($stub->getBrowser());
    }

    public function testSetAndGetArticleNumbers()
    {
        /**
         * @var Transaction $stub
         */
        $stub = $this->getMockForAbstractClass(Transaction::class);
        $stub->setArticleNumbers(['A1', 'A2', 'A3']);

        $this->assertEquals(['A1', 'A2', 'A3'], $stub->getArticleNumbers());
    }
}
