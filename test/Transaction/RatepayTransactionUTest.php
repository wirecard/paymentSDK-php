<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\ItemCollection;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\RatepayTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class RatepayTransactionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RatepayTransaction
     */
    private $tx;

    public function setUp()
    {
        $this->tx = new RatepayTransaction();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }


    public function testSetItemCollection()
    {
        $collection = new ItemCollection();

        $this->tx->setItemCollection($collection);

        $this->assertAttributeEquals($collection, 'itemCollection', $this->tx);
    }

    public function testSetFailureUrl()
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');
        $redirect->method('getFailureUrl')->willReturn('failure-url');

        /**
         * @var Redirect $redirect
         */
        $this->tx->setItemCollection(new ItemCollection());
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->setRedirect($redirect);
        $data = $this->tx->mappedProperties();

        $this->assertArrayHasKey('redirect-url', $data);
    }


    public function testMappedPropertiesSetsOrderItems()
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        /**
         * @var Redirect $redirect
         */
        $this->tx->setItemCollection(new ItemCollection());
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->setRedirect($redirect);
        $data = $this->tx->mappedProperties();

        $this->assertArrayHasKey('order-items', $data);
    }

    public function testGetRetrieveTransactionTypeReserve()
    {
        $this->tx->setItemCollection(new ItemCollection());

        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        $amount = $this->createMock(Amount::class);
        $amount->method('getValue')->willReturn(1.0);

        /**
         * @var Redirect $redirect
         * @var Amount $amount
         */
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount($amount);
        $this->tx->setOperation(Operation::RESERVE);
        $data = $this->tx->mappedProperties();

        $this->assertEquals(Transaction::TYPE_AUTHORIZATION, $data['transaction-type']);
    }

    /**
     * @return array
     */
    public function cancelDataProvider()
    {
        return [
            [Transaction::TYPE_AUTHORIZATION, Transaction::TYPE_VOID_AUTHORIZATION],
            [Transaction::TYPE_CAPTURE_AUTHORIZATION, 'refund-capture'],
        ];
    }

    /**
     * @dataProvider cancelDataProvider
     * @param $transactionType
     * @param $expected
     */
    public function testGetRetrieveTransactionTypeCancel($transactionType, $expected)
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setItemCollection(new ItemCollection());
        $this->tx->setParentTransactionId('1');
        $this->tx->setParentTransactionType($transactionType);
        $data = $this->tx->mappedProperties();
        $this->assertEquals($expected, $data['transaction-type']);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testGetRetrieveTransactionTypeCancelWithoutParentTransactionThrowsException()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setItemCollection(new ItemCollection());
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testGetRetrieveTransactionTypeCancelThrowsException()
    {
        $this->tx->setParentTransactionId('1');
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setItemCollection(new ItemCollection());
        $this->tx->mappedProperties();
    }

    public function testGetRetrieveTransactionTypePay()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setItemCollection(new ItemCollection());
        $this->tx->setParentTransactionId('1');
        $data = $this->tx->mappedProperties();
        $this->assertEquals(Transaction::TYPE_CAPTURE_AUTHORIZATION, $data['transaction-type']);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testGetRetrieveTransactionTypePayThrowsException()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setItemCollection(new ItemCollection());
        $this->tx->mappedProperties();
    }

    public function testGetRetrieveTransactionTypeCredit()
    {
        $this->tx->setOperation(Operation::CREDIT);
        $this->tx->setItemCollection(new ItemCollection());
        $data = $this->tx->mappedProperties();
        $this->assertEquals('credit', $data['transaction-type']);
    }


    public function endpointDataProvider()
    {
        return [
            [Operation::RESERVE, RatepayTransaction::ENDPOINT_PAYMENT_METHODS],
            [Operation::PAY, RatepayTransaction::ENDPOINT_PAYMENTS],
            [Operation::CANCEL, RatepayTransaction::ENDPOINT_PAYMENTS],
        ];
    }

    /**
     * @param $operation
     * @param $expected
     * @dataProvider endpointDataProvider
     */
    public function testGetEndpoint($operation, $expected)
    {
        $this->tx->setOperation($operation);
        $this->assertEquals($expected, $this->tx->getEndpoint());
    }

    public function testSetOrderNumber()
    {
        $orderNr = 123;
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        /**
         * @var Redirect $redirect
         */
        $this->tx->setItemCollection(new ItemCollection());
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->setRedirect($redirect);
        $this->tx->setOrderNumber($orderNr);
        $data = $this->tx->mappedProperties();

        $this->assertEquals($orderNr, $data['order-number']);
    }
}
