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
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class ThreeDCreditCardTransactionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThreeDCreditCardTransaction
     */
    private $tx;

    public function setUp()
    {
        $this->tx = new ThreeDCreditCardTransaction();
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

    public function testMappedPropertiesDefault()
    {
        $expectedResult = [
            'payment-methods' => [
                'payment-method' => [
                    [
                        'name' => 'creditcard'
                    ]
                ]
            ],
            'transaction-type' => 'check-enrollment',
            'card-token' => [
                'token-id' => '33'
            ]
        ];

        $this->tx->setTokenId('33');

        $this->tx->setOperation(Operation::RESERVE);
        $this->assertEquals($expectedResult, $this->tx->mappedProperties());
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
            ]
        ];
        $this->tx->setOperation('testtype');
        $this->assertEquals($valid, $this->tx->mappedProperties());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMapPropertiesNoTokenIdNoParentTransactionIdNoPaRes()
    {
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->mappedProperties();
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
                ThreeDCreditCardTransaction::TYPE_CHECK_ENROLLMENT
            ],
            [
                Operation::RESERVE,
                ThreeDCreditCardTransaction::TYPE_CHECK_ENROLLMENT,
                Transaction::TYPE_AUTHORIZATION
            ],
            [
                Operation::RESERVE,
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_REFERENCED_AUTHORIZATION
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
            ]
        ];

        $amount = new Amount(24, 'EUR');
        $transaction = new ThreeDCreditCardTransaction();
        $transaction->setTokenId('21');
        $transaction->setTermUrl('https://example.com/r');
        $transaction->setAmount($amount);
        $transaction->setParentTransactionId('parent54');
        $transaction->setParentTransactionType($parentTransactionType);
        $transaction->setOperation($operation);
        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testPay3dProvider()
    {
        return [
            [
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_CAPTURE_AUTHORIZATION
            ],
            [
                ThreeDCreditCardTransaction::TYPE_PURCHASE,
                ThreeDCreditCardTransaction::TYPE_REFERENCED_PURCHASE
            ],
            [
                ThreeDCreditCardTransaction::TYPE_CHECK_ENROLLMENT,
                ThreeDCreditCardTransaction::TYPE_PURCHASE
            ],
            [
                null,
                ThreeDCreditCardTransaction::TYPE_CHECK_ENROLLMENT
            ]
        ];
    }

    /**
     * @dataProvider testPay3dProvider
     * @param $transactionType
     * @param $payType
     */
    public function testPay3d($transactionType, $payType)
    {
        $transaction = new ThreeDCreditCardTransaction();
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
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testThreeDCreditCardTransactionThrowsUnsupportedOperationException()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $amount = new Amount(24, 'EUR');
        $transaction = new ThreeDCreditCardTransaction();
        $transaction->setTokenId('21');
        $transaction->setTermUrl('https://example.com/r');
        $transaction->setAmount($amount);
        $transaction->setParentTransactionId('parent54');
        $transaction->setOperation('test');
        $transaction->mappedProperties();
    }
}
