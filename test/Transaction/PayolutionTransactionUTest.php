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

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PayolutionInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceB2BTransaction;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceB2CTransaction;
use Wirecard\PaymentSdk\Transaction\PayolutionTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

require __DIR__ . '/../../vendor/autoload.php';

/*
 * Description of PayolutionTransactionUTest
 *
 * @author Omar Issa
 */

class PayolutionTransactionUTest extends \PHPUnit_Framework_TestCase
{

    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';
    const FAILURE_URL = 'http://www.example.com/failure';

    /**
     * @var PayolutionTransaction
     */
    private $tx;

    private $payoultionType;

    private $accountHolder;

    private $config;

    public function setUp()
    {
        $this->tx = new PayolutionTransaction();
        $this->accountHolder = new AccountHolder();
        $this->payoultionType = new PayolutionInvoiceB2CTransaction();
        $this->config = new PaymentMethodConfig($this->payoultionType->getConfigKey(), 'maid', 'secret');
        $this->accountHolder->setFirstName("Jon");
        $this->accountHolder->setLastName("Doe");
        $this->accountHolder->setDateOfBirth(new \DateTime(1970 - 01 - 01));
        $this->tx->setAmount(new Amount(150, 'EUR'));
        $this->tx->setAccountHolder($this->accountHolder);
    }


    public function testSetShipping()
    {
        $accountHolder = new AccountHolder();

        $this->tx->setShipping($accountHolder);

        $this->assertAttributeEquals($accountHolder, 'shipping', $this->tx);
    }

    public function testMappedProperties()
    {

        $expectedResult = [
            'transaction-type' => Transaction::TYPE_AUTHORIZATION,
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '150'
            ],
            'account-holder' => array(
                'last-name' => 'Doe',
                'first-name' => 'Jon',
                'date-of-birth' => '17-08-1968'
            ),

            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'payolution'
                    ]
                ]
            ],
            'cancel-redirect-url' => self::CANCEL_URL,
            'success-redirect-url' => self::SUCCESS_URL,
            'fail-redirect-url' => self::FAILURE_URL,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',

        ];
        $this->tx->setPayoultionType($this->payoultionType->getConfigKey());
        $redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL, self::FAILURE_URL);
        $this->tx->setRedirect($redirect);
        $this->tx->setParentTransactionType(Transaction::PARAM_TRANSACTION_TYPE);
        $this->tx->setOperation(Operation::PAY);
        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
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


    public function testRefundProvider()
    {
        return [
            [
                Transaction::TYPE_CAPTURE_AUTHORIZATION,
                'refund-capture'
            ]
        ];
    }

    /**
     * @dataProvider testRefundProvider
     * @param $transactionType
     * @param $refundType
     */
    public function testRefund($value, $expected)
    {
        $this->tx->setConfig($this->config);
        $this->tx->setParentTransactionId('642');
        $this->tx->setParentTransactionType(Transaction::TYPE_CAPTURE_AUTHORIZATION);
        $this->tx->setOperation(Operation::REFUND);
        $this->tx->setAmount(new Amount(150, 'EUR'));
        $result = $this->tx->mappedProperties();


        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'payolution-inv']]],
            'parent-transaction-id' => '642',
            'transaction-type' => 'refund-capture',

            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '150'
            ],
            'account-holder' => array(
                'last-name' => 'Doe',
                'first-name' => 'Jon',
                'date-of-birth' => '17-08-1968'
            ),

            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'payolution'
                    ]
                ]
            ],
            'locale' => 'de',
            'entry-mode' => 'ecommerce',

        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testInvoiceB2B()
    {
        $invoice = new PayolutionInvoiceB2BTransaction();
        $this->assertEquals('payolution-b2b', $invoice->getConfigKey());
    }

    public function testInvoiceB2C()
    {
        $invoice = new PayolutionInvoiceB2CTransaction();
        $this->assertEquals('payolution-inv', $invoice->getConfigKey());
    }

    public function testInstallment()
    {
        $invoice = new PayolutionInstallmentTransaction();
        $this->assertEquals('payolution-inst', $invoice->getConfigKey());
    }


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

    protected function tearDown()
    {
        $this->tx = null;
    }
}
