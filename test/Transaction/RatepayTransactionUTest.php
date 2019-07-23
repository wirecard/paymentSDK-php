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
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class RatepayTransactionUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RatepayTransaction
     */
    private $tx;

    public function setUp()
    {
        $this->tx = new RatepayTransaction();
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

    public function testSetBasket()
    {
        $collection = new Basket();

        $this->tx->setBasket($collection);

        $this->assertAttributeEquals($collection, 'basket', $this->tx);
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
        $this->tx->setBasket(new Basket());
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->setRedirect($redirect);
        $data = $this->tx->mappedProperties();

        $this->assertEquals('failure-url', $data['fail-redirect-url']);
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
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->setRedirect($redirect);
        $data = $this->tx->mappedProperties();

        $this->assertArrayHasKey('order-items', $data);
    }

    public function testGetRetrieveTransactionTypeReserve()
    {
        $this->tx->setBasket(new Basket());

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
        $this->tx->setBasket(new Basket());
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
        $this->tx->setBasket(new Basket());
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testGetRetrieveTransactionTypeCancelThrowsException()
    {
        $this->tx->setParentTransactionId('1');
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setBasket(new Basket());
        $this->tx->mappedProperties();
    }

    public function testGetRetrieveTransactionTypePay()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setBasket(new Basket());
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
        $this->tx->setBasket(new Basket());
        $this->tx->mappedProperties();
    }

    public function testGetRetrieveTransactionTypeCredit()
    {
        $this->tx->setOperation(Operation::CREDIT);
        $this->tx->setBasket(new Basket());
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
        $this->tx->setBasket(new Basket());
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->setRedirect($redirect);
        $this->tx->setOrderNumber($orderNr);
        $data = $this->tx->mappedProperties();

        $this->assertEquals($orderNr, $data['order-number']);
    }

    public function testSetDevice()
    {
        $fingerprint = "ABCD1234EFG";
        $device = new Device();
        $device->setFingerprint($fingerprint);
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');

        /**
         * @var Redirect $redirect
         */
        $this->tx->setBasket(new Basket());
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->setRedirect($redirect);
        $this->tx->setDevice($device);
        $data = $this->tx->mappedProperties();

        $this->assertEquals($device->mappedProperties(), $data['device']);
    }

    public function testInvoice()
    {
        $invoice = new RatepayInvoiceTransaction();
        $this->assertEquals('ratepay-invoice', $invoice->getConfigKey());
    }

    public function testInstallment()
    {
        $invoice = new RatepayInstallmentTransaction();
        $this->assertEquals('ratepay-install', $invoice->getConfigKey());
    }
}
