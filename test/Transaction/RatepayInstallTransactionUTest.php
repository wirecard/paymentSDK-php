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
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\RatepayInstallTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class RatepayInstallTransactionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RatepayInstallTransaction
     */
    private $tx;

    public function setUp()
    {
        $this->tx = new RatepayInstallTransaction();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }

    public function reserveDataProvider()
    {
        return [
            [1.0, Transaction::TYPE_AUTHORIZATION],
            [0.0, 'authorization-only']
        ];
    }


    public function testSetItemCollection()
    {
        $collection = new ItemCollection();

        $this->tx->setItemCollection($collection);

        $this->assertAttributeEquals($collection, 'itemCollection', $this->tx);
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
        $this->tx->setOperation('pay');
        $this->tx->setRedirect($redirect);
        $data = $this->tx->mappedProperties();

        $this->assertArrayHasKey('order-items', $data);
    }

    /**
     * @param float $amount
     * @param string $expected
     * @dataProvider reserveDataProvider
     */
    public function testGetRetrieveTransactionTypeReserve($amount, $expected)
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        $money = $this->createMock(Money::class);
        $money->method('getAmount')->willReturn($amount);

        /**
         * @var Redirect $redirect
         * @var Money $money
         */
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount($money);
        $this->tx->setOperation('reserve');
        $data = $this->tx->mappedProperties();

        $this->assertEquals($expected, $data['transaction-type']);
    }

    public function payDataProvider()
    {
        return [
            [null, Transaction::TYPE_DEBIT],
            [Transaction::TYPE_AUTHORIZATION, Transaction::TYPE_CAPTURE_AUTHORIZATION]
        ];
    }

    /**
     * @param string $parentTransactionType
     * @param string $expected
     * @dataProvider payDataProvider
     */
    public function testGetRetrieveTransactionTypePay($parentTransactionType, $expected)
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');
        $redirect->method('getFailureUrl')->willReturn('failure-url');

        $money = $this->createMock(Money::class);
        $money->method('getAmount')->willReturn(1.00);

        /**
         * @var Redirect $redirect
         * @var Money $money
         */
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount($money);
        $this->tx->setParentTransactionType($parentTransactionType);
        $this->tx->setOperation('pay');
        $data = $this->tx->mappedProperties();

        $this->assertEquals($expected, $data['transaction-type']);
    }

    public function testGetRetrieveTransactionTypeCredit()
    {
        $amount = 1.00;
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');
        $redirect->method('getFailureUrl')->willReturn('failure-url');

        $money = $this->createMock(Money::class);
        $money->method('getAmount')->willReturn($amount);

        /**
         * @var Redirect $redirect
         * @var Money $money
         */
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount($money);

        $this->tx->setOperation('credit');

        $data = $this->tx->mappedProperties();

        $this->assertEquals('pending-credit', $data['transaction-type']);
    }


    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testGetRetrieveTransactionTypeCancelWithoutParent()
    {
        $this->tx->setOperation('cancel');
        $this->tx->mappedProperties();
    }

    public function debitDataProvider()
    {
        return [
            [Transaction::TYPE_AUTHORIZATION, Transaction::TYPE_VOID_AUTHORIZATION],
            [Transaction::TYPE_DEBIT, 'refund-debit'],
            [Transaction::TYPE_CAPTURE_AUTHORIZATION, 'refund-capture']
        ];
    }

    /**
     * @param string $parentTransactionType
     * @param string $expected
     * @dataProvider debitDataProvider
     */
    public function testGetRetrieveTransactionTypeCancel($parentTransactionType, $expected)
    {
        $this->tx->setParentTransactionType($parentTransactionType);
        $this->tx->setOperation('cancel');

        $data = $this->tx->mappedProperties();

        $this->assertEquals($expected, $data['transaction-type']);
    }

    public function testGetEndpointWithParent()
    {
        $this->tx->setParentTransactionId('gfghfgh');
        $this->assertEquals(Transaction::ENDPOINT_PAYMENTS, $this->tx->getEndpoint());
    }

    public function testGetEndpoint()
    {
        $this->assertEquals(Transaction::ENDPOINT_PAYMENT_METHODS, $this->tx->getEndpoint());
    }
}
