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
use Wirecard\PaymentSdk\Transaction\PayolutionTransaction;
use Wirecard\PaymentSdk\Transaction\PayolutionInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceB2BTransaction;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceB2CTransaction;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
/*
 * Description of PayolutionTransactionUTest
 *
 * @author Omar Issa
 */

class PayolutionTransactionUTest extends \PHPUnit_Framework_TestCase {

    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';
    const FAILURE_URL = 'http://www.example.com/failure';

    /**
     * @var PayolutionTransaction
     */
    private $tx;

    public function setUp() {
        $this->tx = new PayolutionTransaction();
        $accountHolder = new AccountHolder();
        $accountHolder->setFirstName("Firstname");
        $accountHolder->setLastName("Lastname");
        $this->tx->setAmount(new Amount(45, 'EUR'));
        $this->tx->setAccountHolder($accountHolder);
        $redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL, self::FAILURE_URL);
        $this->tx->setRedirect($redirect);
    }


    public function testSetBasket() {
        $collection = new Basket();

        $this->tx->setBasket($collection);

        $this->assertAttributeEquals($collection, 'basket', $this->tx);
    }

    public function testSetShipping() {
        $accountHolder = new AccountHolder();

        $this->tx->setShipping($accountHolder);

        $this->assertAttributeEquals($accountHolder, 'shipping', $this->tx);
    }

    public function testMappedProperties() {
        $expectedResult = [
            'transaction-type' => Transaction::authorization,
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '45'
            ],
            'account-holder' => array(),
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'payolution-inv'
                    ]
                ]
            ],
        ];

        $this->tx->setParentTransactionType(Transaction::authorization);
        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
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
     * @dataProvider testRefundProvider
     * @param $transactionType
     * @param $refundType
     */
    public function testRefund()
    {
        $this->tx->setConfig($this->config);
        $this->tx->setParentTransactionId('642');
        $this->tx->setParentTransactionType(Transaction::TYPE_REFUND_CAPTURE);
        $this->tx->setOperation(Operation::REFUND);
        $result = $tx->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'payolution-b2b']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => 'refund-capture',
            'merchant-account-id' => [
                'value' => 'maid'
            ],
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testInvoiceB2B() {
        $invoice = new PayolutionInvoiceB2BTransaction();
        $this->assertEquals('payolution-b2b', $invoice->getConfigKey());
    }

    public function testInvoiceB2C() {
        $invoice = new PayolutionInvoiceB2CTransaction();
        $this->assertEquals('payolution-b2c', $invoice->getConfigKey());
    }

    public function testInstallment() {
        $invoice = new PayolutionInstallmentTransaction();
        $this->assertEquals('payolution-inst', $invoice->getConfigKey());
    }

}
