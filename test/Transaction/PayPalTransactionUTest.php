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
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Browser;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class PayPalTransactionUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PayPalTransaction
     */
    private $tx;

    public function setUp()
    {
        $this->tx = new PayPalTransaction();
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

    public function reserveDataProvider()
    {
        return [
            [1.0, Transaction::TYPE_AUTHORIZATION],
            [0.0, 'authorization-only']
        ];
    }


    public function testSetBasket()
    {
        $collection = new Basket();

        $this->tx->setBasket($collection);

        $this->assertAttributeEquals($collection, 'basket', $this->tx);
    }

    public function testSetShipping()
    {
        $accountHolder = new AccountHolder();

        $this->tx->setShipping($accountHolder);

        $this->assertAttributeEquals($accountHolder, 'shipping', $this->tx);
    }

    public function testMappedPropertiesSetsOptional()
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        /**
         * @var Redirect $redirect
         */
        $this->tx->setShipping(new AccountHolder());
        $this->tx->setOrderNumber('order number 13');
        $this->tx->setOrderDetail('order-detail my');
        $this->tx->setDescriptor('descriptor');
        $this->tx->setOperation('pay');
        $this->tx->setRedirect($redirect);
        $this->tx->setBrowser(new Browser('application/xml'));
        $data = $this->tx->mappedProperties();

        $expected = [
            'payment-methods' => [
                'payment-method' => [
                    [
                        'name' => 'paypal'
                    ]
                ]
            ],
            'success-redirect-url' => 'success-url',
            'cancel-redirect-url' => 'cancel-url',
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'transaction-type' => 'debit',
            'shipping' => [],
            'order-number' => 'order number 13',
            'order-detail' => 'order-detail my',
            'descriptor' => 'descriptor',
            'browser' => ['accept' => 'application/xml'],
            'ip-address' => '0.0.0.1'
        ];

        $this->assertEquals($expected, $data);
    }

    public function testMappedPropertiesSetsOrderItems()
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        /**
         * @var Redirect $redirect
         */
        $this->tx->setBasket(new Basket());
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
    public function testGetRetrieveTransactionTypeReserve($value, $expected)
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        $amount = $this->createMock(Amount::class);
        $amount->method('getValue')->willReturn($value);

        /**
         * @var Redirect $redirect
         * @var Amount $amount
         */
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount($amount);
        $this->tx->setOperation(Operation::RESERVE);
        $data = $this->tx->mappedProperties();

        $this->assertEquals($expected, $data['transaction-type']);
    }

    public function payDataProvider()
    {
        return [
            [null, Transaction::TYPE_DEBIT],
            [Transaction::TYPE_AUTHORIZATION, Transaction::TYPE_CAPTURE_AUTHORIZATION],
            [Transaction::TYPE_DEBIT, Transaction::TYPE_DEBIT]
        ];
    }

    public function payDataProviderException()
    {
        return [
            [Transaction::TYPE_CHECK_ENROLLMENT, Transaction::TYPE_DEBIT]
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

        $amount = $this->createMock(Amount::class);
        $amount->method('getValue')->willReturn(1.00);

        /**
         * @var Redirect $redirect
         * @var Amount $amount
         */
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount($amount);
        $this->tx->setParentTransactionType($parentTransactionType);
        $this->tx->setOperation('pay');
        $data = $this->tx->mappedProperties();

        $this->assertEquals($expected, $data['transaction-type']);
    }

    /**
     * @param string $parentTransactionType
     * @param string $expected
     * @dataProvider payDataProviderException
     */
    public function testGetRetrieveTransactionTypePayException($parentTransactionType, $expected)
    {
        $this->expectException(UnsupportedOperationException::class);

        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        $amount = $this->createMock(Amount::class);
        $amount->method('getValue')->willReturn(1.00);

        /**
         * @var Redirect $redirect
         * @var Amount $amount
         */
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount($amount);
        $this->tx->setParentTransactionType($parentTransactionType);
        $this->tx->setOperation('pay');
        $data = $this->tx->mappedProperties();

        $this->assertEquals($expected, $data['transaction-type']);
    }

    public function testGetRetrieveTransactionTypeCredit()
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        $amount = $this->createMock(Amount::class);
        $amount->method('getValue')->willReturn(1.00);

        /**
         * @var Redirect $redirect
         * @var Amount $amount
         */
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount($amount);

        $this->tx->setOperation('credit');

        $data = $this->tx->mappedProperties();

        $this->assertEquals('pending-credit', $data['transaction-type']);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testGetRetrieveTransactionTypeCancelWithoutParentTransaction()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testGetRetrieveTransactionTypeCancelWithInvalidParentTransactionType()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setParentTransactionId('1');
        $this->tx->mappedProperties();
    }

    public function debitDataProvider()
    {
        return [
            [Transaction::TYPE_AUTHORIZATION, Transaction::TYPE_VOID_AUTHORIZATION],
            [Transaction::TYPE_DEBIT, Transaction::TYPE_REFUND_DEBIT],
            [Transaction::TYPE_CAPTURE_AUTHORIZATION, Transaction::TYPE_REFUND_CAPTURE]
        ];
    }

    /**
     * @param string $parentTransactionType
     * @param string $expected
     * @dataProvider debitDataProvider
     */
    public function testGetRetrieveTransactionTypeCancel($parentTransactionType, $expected)
    {
        $this->tx->setParentTransactionId('1');
        $this->tx->setParentTransactionType($parentTransactionType);
        $this->tx->setOperation(Operation::CANCEL);

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

    public function testDescriptor()
    {
        $descriptor = "0123-€\$§%!=#~;+/?:().,'&><\"*{}[]@\_°^|ÄÖÜäöüß^°`" .
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        // Only 27 chars are allowed
        $expectedDescriptor = "0123-+.,'ÄÖÜäöüabcdefghijkl";
        $transaction = new PayPalTransaction();
        $transaction->setDescriptor($descriptor);
        $this->assertEquals($expectedDescriptor, $transaction->getDescriptor());
    }
}
