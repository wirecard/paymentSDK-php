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

use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

class CreditCardTransactionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CreditCardTransaction
     */
    private $tx;

    /**
     * @var CreditCardConfig
     */
    private $config;

    public function setUp()
    {
        $this->config = new CreditCardConfig('maid', 'secret');
        $this->tx = new CreditCardTransaction();
        $this->tx->setConfig($this->config);
    }

    public function testSetTermUrl()
    {
        $this->tx->setTermUrl('test');
        $this->assertAttributeEquals('test', 'termUrl', $this->tx);
    }

    public function testGetTermUrl()
    {
        $this->tx->setTermUrl('test');
        $this->assertEquals('test', $this->tx->getTermUrl());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setTokenId('anything');

        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMapPropertiesNoTokenIdNoParentTransactionId()
    {
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->mappedProperties();
    }

    public function testSslCreditCardTransactionWithTokenId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'transaction-type' => 'authorization',
            'card-token' => [
                'token-id' => '21'
            ],
            'ip-address' => 'test IP',
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];

        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setTokenId('21');
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setOperation(Operation::RESERVE);

        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testMappedPropertiesPares()
    {
        $this->tx->setPaRes('pasdsgf');
        $valid = [
            'payment-methods' => [
                'payment-method' => [
                    [
                        'name' => 'creditcard'
                    ]
                ]
            ],
            'transaction-type' => 'testtype',
            'three-d' => [
                'pares' => 'pasdsgf'
            ],
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->tx->setOperation('testtype');
        $this->assertEquals($valid, $this->tx->mappedProperties());
    }

    public function testSslCreditCardTransactionWithParentTransactionId()
    {

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent5',
            'transaction-type' => 'referenced-authorization',
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];

        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setParentTransactionId('parent5');
        $transaction->setParentTransactionType(Transaction::TYPE_AUTHORIZATION);
        $transaction->setOperation(Operation::RESERVE);
        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testSslCreditCardTransactionWithoutTokenIdAndParentTransactionId()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setOperation(Operation::RESERVE);
        $transaction->mappedProperties();
    }

    public function testSslCreditCardTransactionWithBothTokenIdAndParentTransactionId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent5',
            'ip-address' => 'test IP',
            'transaction-type' => 'referenced-authorization',
            'card-token' => [
                'token-id' => '33'
            ],
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];

        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setTokenId('33');
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setParentTransactionId('parent5');
        $transaction->setParentTransactionType(Transaction::TYPE_AUTHORIZATION);
        $transaction->setOperation(Operation::RESERVE);
        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function testCancelProvider()
    {
        return [
            [
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_VOID_AUTHORIZATION
            ],
            [
                Transaction::TYPE_REFERENCED_AUTHORIZATION,
                Transaction::TYPE_VOID_AUTHORIZATION
            ],
            [
                'refund-capture',
                'void-refund-capture'
            ],
            [
                'refund-purchase',
                'void-refund-purchase'
            ],
            [
                Transaction::TYPE_CREDIT,
                'void-credit'
            ],
            [
                CreditCardTransaction::TYPE_PURCHASE,
                'void-purchase'
            ],
            [
                CreditCardTransaction::TYPE_REFERENCED_PURCHASE,
                'void-purchase'
            ],
            [
                Transaction::TYPE_CAPTURE_AUTHORIZATION,
                'void-capture'
            ]
        ];
    }

    /**
     * @dataProvider testCancelProvider
     * @param $transactionType
     * @param $cancelType
     */
    public function testCancel($transactionType, $cancelType)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType($transactionType);
        $transaction->setOperation(Operation::CANCEL);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => $cancelType,
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testPayProvider()
    {
        return [
            [
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_CAPTURE_AUTHORIZATION
            ],
            [
                CreditCardTransaction::TYPE_PURCHASE,
                CreditCardTransaction::TYPE_REFERENCED_PURCHASE
            ],
            [
                CreditCardTransaction::TYPE_CHECK_ENROLLMENT,
                CreditCardTransaction::TYPE_PURCHASE
            ],
            [
                null,
                CreditCardTransaction::TYPE_PURCHASE
            ]
        ];
    }

    /**
     * @dataProvider testPayProvider
     * @param $transactionType
     * @param $payType
     */
    public function testPay($transactionType, $payType)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType($transactionType);
        $transaction->setOperation(Operation::PAY);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => $payType,
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testCancelNoParentId()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setOperation(Operation::CANCEL);
        $transaction->mappedProperties();
    }


    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testCancelInvalidParentTransaction()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType('test');
        $transaction->setOperation(Operation::CANCEL);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $transaction->mappedProperties();
    }

    /**
     * @return array
     */
    public function testRefundProvider()
    {
        return [
            [
                CreditCardTransaction::TYPE_PURCHASE,
                'refund-purchase'
            ],
            [
                CreditCardTransaction::TYPE_REFERENCED_PURCHASE,
                'refund-purchase'
            ],
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
    public function testRefund($transactionType, $refundType)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType($transactionType);
        $transaction->setOperation(Operation::REFUND);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => $refundType,
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testRefundNoParentId()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setOperation(Operation::REFUND);
        $transaction->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testRefundInvalidParentTransaction()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType('test');
        $transaction->setOperation(Operation::REFUND);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $transaction->mappedProperties();
    }

    public function testCredit()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType(Transaction::TYPE_CREDIT);
        $transaction->setOperation(Operation::CREDIT);

        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => 'credit',
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testRetrieveOperationTypeAuthorization()
    {
        $tx = new CreditCardTransaction();
        $tx->setConfig($this->config);
        $tx->setOperation(Operation::RESERVE);

        $this->assertEquals(Transaction::TYPE_AUTHORIZATION, $tx->retrieveOperationType());
    }

    public function testRetrieveOperationTypePurchase()
    {
        $tx = new CreditCardTransaction();
        $tx->setConfig($this->config);
        $tx->setOperation(Operation::PAY);

        $this->assertEquals(CreditCardTransaction::TYPE_PURCHASE, $tx->retrieveOperationType());
    }

    public function threeDProvider()
    {
        return [
            [
                Operation::CANCEL,
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_VOID_AUTHORIZATION
            ],
            [
                Operation::RESERVE,
                null,
                CreditCardTransaction::TYPE_CHECK_ENROLLMENT
            ],
            [
                Operation::RESERVE,
                CreditCardTransaction::TYPE_CHECK_ENROLLMENT,
                Transaction::TYPE_AUTHORIZATION
            ],
            [
                Operation::RESERVE,
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_REFERENCED_AUTHORIZATION
            ],
            [
                Operation::PAY,
                null,
                CreditCardTransaction::TYPE_CHECK_ENROLLMENT
            ],
        ];
    }
    /**
     * @param $operation
     * @param $parentTransactionType
     * @param $expectedType
     * @dataProvider threeDProvider
     */
    public function testThreeDCreditCardTransaction($operation, $parentTransactionType, $expectedType)
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';
        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent54',
            'ip-address' => 'test IP',
            'transaction-type' => $expectedType,
            'card-token' => [
                'token-id' => '21'
            ],
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'locale' => 'de',
            'entry-mode' => 'telephone',
        ];
        $this->config->addSslMaxLimit(new Amount(20.0, 'EUR'));
        $this->config->setThreeDCredentials('maid', '123abcd');
        $amount = new Amount(24, 'EUR');
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setTokenId('21');
        $transaction->setTermUrl('https://example.com/r');
        $transaction->setAmount($amount);
        $transaction->setParentTransactionId('parent54');
        $transaction->setParentTransactionType($parentTransactionType);
        $transaction->setOperation($operation);
        $transaction->setEntryMode('telephone');
        $transaction->setLocale('de');
        $result = $transaction->mappedProperties();
        $this->assertEquals($expectedResult, $result);
    }

    public function testIsThreeDWithSetThreeD()
    {
        $this->tx->setThreeD(false);
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setTokenId('21');

        $result = $this->tx->mappedProperties();

        $this->assertEquals(CreditCardTransaction::TYPE_PURCHASE, $result['transaction-type']);
    }

    public function testIsThreeDWithThreeDMinLimit()
    {
        $this->config->addThreeDMinLimit(new Amount(20.0, 'EUR'));
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setTokenId('21');
        $this->tx->setAmount(new Amount(20.1, 'EUR'));

        $result = $this->tx->mappedProperties();

        $this->assertEquals(CreditCardTransaction::TYPE_CHECK_ENROLLMENT, $result['transaction-type']);
    }

    /**
     * @return array
     */
    public function isFallbackProvider()
    {
        return [
            [false, null, null, null],
            [false, new Amount(70.0, 'EUR'), null, null],
            [
                true,
                new Amount(70.0, 'EUR'),
                null,
                new Amount(50.0, 'EUR')
            ],
            [
                true,
                new Amount(70.0, 'EUR'),
                new Amount(100.0, 'EUR'),
                new Amount(50.0, 'EUR')
            ]
        ];
    }

    /**
     * @dataProvider isFallbackProvider
     * @param $expected
     * @param $amount
     * @param $sslMaxLimit
     * @param $threeDMinLimit
     */
    public function testIsFallback($expected, $amount, $sslMaxLimit, $threeDMinLimit)
    {
        if (null !== $amount) {
            $this->tx->setAmount($amount);
        }

        if (null !== $sslMaxLimit) {
            $this->config->addSslMaxLimit($sslMaxLimit);
        }

        if (null !== $threeDMinLimit) {
            $this->config->addThreeDMinLimit($threeDMinLimit);
        }

        $this->assertEquals($expected, $this->tx->isFallback());
    }
}
