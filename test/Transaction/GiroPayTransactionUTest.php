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
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Browser;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;
use Wirecard\PaymentSdk\Transaction\GiroPayTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class GiroPayTransactionUTest extends \PHPUnit_Framework_TestCase
{
    private $tx;

    public function setUp()
    {
        $this->tx = new GiroPayTransaction();
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

        $this->assertInstanceOf(GiroPayTransaction::class, $orderDetail);
    }

    public function testsetBankData()
    {
        $bankAccountMock = $this->createMock(BankAccount::class);

        $bankAccountMock->method('mappedProperties')
            ->willReturn(new BankAccount());

        $return = $this->tx->setBankAccount($bankAccountMock);

        $this->assertInstanceOf(GiroPayTransaction::class, $return);

    }

    public static function callMethod($obj, $name, $args=[])
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

        $returnType = $this->callMethod($this->tx,'retrieveTransactionTypeForPay');

        $this->assertEquals('get-url',$returnType);
    }

    public function testShouldReturnEndPointParent()
    {

        $reflectionClass = new ReflectionClass($this->tx);

        $property = $reflectionClass->getProperty('parentTransactionId');
        $property->setAccessible(true);
        $property->setValue($this->tx,'test-with-parent-id');

        $method = $reflectionClass->getMethod('getEndpoint');
        $method->setAccessible(true);

        $newMethod = $method->invokeArgs($this->tx,[]);

        $this->assertEquals($newMethod,'/engine/rest/payments/');

    }

    public function testShouldReturnEndPointNotParent()
    {
        $returnType = $this->callMethod($this->tx,'getEndpoint');

        $this->assertEquals($returnType,'/engine/rest/paymentmethods/');
    }


//    public function reserveDataProvider()
//    {
//        return [
//            [1.0, Transaction::TYPE_AUTHORIZATION],
//            [0.0, 'authorization-only']
//        ];
//    }
//
//
//    public function testSetBasket()
//    {
//        $collection = new Basket();
//
//        $this->tx->setBasket($collection);
//
//        $this->assertAttributeEquals($collection, 'basket', $this->tx);
//    }
//
//    public function testSetShipping()
//    {
//        $accountHolder = new AccountHolder();
//
//        $this->tx->setShipping($accountHolder);
//
//        $this->assertAttributeEquals($accountHolder, 'shipping', $this->tx);
//    }
//
//    public function testMappedPropertiesSetsOptional()
//    {
//        $redirect = $this->createMock(Redirect::class);
//        $redirect->method('getCancelUrl')->willReturn('cancel-url');
//        $redirect->method('getSuccessUrl')->willReturn('success-url');
//
//        /**
//         * @var Redirect $redirect
//         */
//        $this->tx->setShipping(new AccountHolder());
//        $this->tx->setOrderNumber('order number 13');
//        $this->tx->setOrderDetail('order-detail my');
//        $this->tx->setDescriptor('descriptor');
//        $this->tx->setOperation('pay');
//        $this->tx->setRedirect($redirect);
//        $this->tx->setBrowser(new Browser('application/xml'));
//        $data = $this->tx->mappedProperties();
//
//        $expected = [
//            'payment-methods' => [
//                'payment-method' => [
//                    [
//                        'name' => 'paypal'
//                    ]
//                ]
//            ],
//            'success-redirect-url' => 'success-url',
//            'cancel-redirect-url' => 'cancel-url',
//            'locale' => 'de',
//            'entry-mode' => 'ecommerce',
//            'transaction-type' => 'debit',
//            'shipping' => [],
//            'order-number' => 'order number 13',
//            'order-detail' => 'order-detail my',
//            'descriptor' => 'descriptor',
//            'browser' => ['accept' => 'application/xml']
//        ];
//
//        $this->assertEquals($expected, $data);
//    }
//
//    public function testMappedPropertiesSetsOrderItems()
//    {
//        $redirect = $this->createMock(Redirect::class);
//        $redirect->method('getCancelUrl')->willReturn('cancel-url');
//        $redirect->method('getSuccessUrl')->willReturn('success-url');
//
//        /**
//         * @var Redirect $redirect
//         */
//        $this->tx->setBasket(new Basket());
//        $this->tx->setOperation('pay');
//        $this->tx->setRedirect($redirect);
//        $data = $this->tx->mappedProperties();
//
//        $this->assertArrayHasKey('order-items', $data);
//    }
//
//    /**
//     * @param float $amount
//     * @param string $expected
//     * @dataProvider reserveDataProvider
//     */
//    public function testGetRetrieveTransactionTypeReserve($value, $expected)
//    {
//        $redirect = $this->createMock(Redirect::class);
//        $redirect->method('getCancelUrl')->willReturn('cancel-url');
//        $redirect->method('getSuccessUrl')->willReturn('success-url');
//
//        $amount = $this->createMock(Amount::class);
//        $amount->method('getValue')->willReturn($value);
//
//        /**
//         * @var Redirect $redirect
//         * @var Amount $amount
//         */
//        $this->tx->setRedirect($redirect);
//        $this->tx->setAmount($amount);
//        $this->tx->setOperation(Operation::RESERVE);
//        $data = $this->tx->mappedProperties();
//
//        $this->assertEquals($expected, $data['transaction-type']);
//    }
//
//    public function payDataProvider()
//    {
//        return [
//            [null, Transaction::TYPE_DEBIT],
//            [Transaction::TYPE_AUTHORIZATION, Transaction::TYPE_CAPTURE_AUTHORIZATION],
//            [Transaction::TYPE_DEBIT, Transaction::TYPE_DEBIT]
//        ];
//    }
//
//    public function payDataProviderException()
//    {
//        return [
//            [Transaction::TYPE_CHECK_ENROLLMENT, Transaction::TYPE_DEBIT]
//        ];
//    }
//
//    /**
//     * @param string $parentTransactionType
//     * @param string $expected
//     * @dataProvider payDataProvider
//     */
//    public function testGetRetrieveTransactionTypePay($parentTransactionType, $expected)
//    {
//        $redirect = $this->createMock(Redirect::class);
//        $redirect->method('getCancelUrl')->willReturn('cancel-url');
//        $redirect->method('getSuccessUrl')->willReturn('success-url');
//
//        $amount = $this->createMock(Amount::class);
//        $amount->method('getValue')->willReturn(1.00);
//
//        /**
//         * @var Redirect $redirect
//         * @var Amount $amount
//         */
//        $this->tx->setRedirect($redirect);
//        $this->tx->setAmount($amount);
//        $this->tx->setParentTransactionType($parentTransactionType);
//        $this->tx->setOperation('pay');
//        $data = $this->tx->mappedProperties();
//
//        $this->assertEquals($expected, $data['transaction-type']);
//    }
//
//    /**
//     * @param string $parentTransactionType
//     * @param string $expected
//     * @dataProvider payDataProviderException
//     */
//    public function testGetRetrieveTransactionTypePayException($parentTransactionType, $expected)
//    {
//        $this->expectException(UnsupportedOperationException::class);
//
//        $redirect = $this->createMock(Redirect::class);
//        $redirect->method('getCancelUrl')->willReturn('cancel-url');
//        $redirect->method('getSuccessUrl')->willReturn('success-url');
//
//        $amount = $this->createMock(Amount::class);
//        $amount->method('getValue')->willReturn(1.00);
//
//        /**
//         * @var Redirect $redirect
//         * @var Amount $amount
//         */
//        $this->tx->setRedirect($redirect);
//        $this->tx->setAmount($amount);
//        $this->tx->setParentTransactionType($parentTransactionType);
//        $this->tx->setOperation('pay');
//        $data = $this->tx->mappedProperties();
//
//        $this->assertEquals($expected, $data['transaction-type']);
//    }
//
//    public function testGetRetrieveTransactionTypeCredit()
//    {
//        $redirect = $this->createMock(Redirect::class);
//        $redirect->method('getCancelUrl')->willReturn('cancel-url');
//        $redirect->method('getSuccessUrl')->willReturn('success-url');
//
//        $amount = $this->createMock(Amount::class);
//        $amount->method('getValue')->willReturn(1.00);
//
//        /**
//         * @var Redirect $redirect
//         * @var Amount $amount
//         */
//        $this->tx->setRedirect($redirect);
//        $this->tx->setAmount($amount);
//
//        $this->tx->setOperation('credit');
//
//        $data = $this->tx->mappedProperties();
//
//        $this->assertEquals('pending-credit', $data['transaction-type']);
//    }
//
//    /**
//     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
//     */
//    public function testGetRetrieveTransactionTypeCancelWithoutParentTransaction()
//    {
//        $this->tx->setOperation(Operation::CANCEL);
//        $this->tx->mappedProperties();
//    }
//
//    /**
//     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
//     */
//    public function testGetRetrieveTransactionTypeCancelWithInvalidParentTransactionType()
//    {
//        $this->tx->setOperation(Operation::CANCEL);
//        $this->tx->setParentTransactionId('1');
//        $this->tx->mappedProperties();
//    }
//
//    public function debitDataProvider()
//    {
//        return [
//            [Transaction::TYPE_AUTHORIZATION, Transaction::TYPE_VOID_AUTHORIZATION],
//            [Transaction::TYPE_DEBIT, Transaction::TYPE_REFUND_DEBIT],
//            [Transaction::TYPE_CAPTURE_AUTHORIZATION, Transaction::TYPE_REFUND_CAPTURE]
//        ];
//    }
//
//    /**
//     * @param string $parentTransactionType
//     * @param string $expected
//     * @dataProvider debitDataProvider
//     */
//    public function testGetRetrieveTransactionTypeCancel($parentTransactionType, $expected)
//    {
//        $this->tx->setParentTransactionId('1');
//        $this->tx->setParentTransactionType($parentTransactionType);
//        $this->tx->setOperation(Operation::CANCEL);
//
//        $data = $this->tx->mappedProperties();
//
//        $this->assertEquals($expected, $data['transaction-type']);
//    }
//
//    public function testGetEndpointWithParent()
//    {
//        $this->tx->setParentTransactionId('gfghfgh');
//        $this->assertEquals(Transaction::ENDPOINT_PAYMENTS, $this->tx->getEndpoint());
//    }
//
//    public function testGetEndpoint()
//    {
//        $this->assertEquals(Transaction::ENDPOINT_PAYMENT_METHODS, $this->tx->getEndpoint());
//    }
}
