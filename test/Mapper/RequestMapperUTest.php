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

namespace WirecardTest\PaymentSdk\Mapper;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Config\PaymentMethodConfigCollection;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Mapper\RequestMapper;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class RequestMapperUTest extends \PHPUnit_Framework_TestCase
{
    const MAID = 'B612';

    const EXAMPLE_URL = 'http://www.example.com';

    /**
     * @var RequestMapper
     */
    private $mapper;

    protected function setUp()
    {
        $this->mapper = $this->createRequestMapper();
    }
    
    public function testPayPalTransaction()
    {
        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'paypal']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'notifications' => ['notification' => [['url' => self::EXAMPLE_URL]]],
            'transaction-type' => 'debit',
            'cancel-redirect-url' => 'http://www.example.com/cancel',
            'success-redirect-url' => 'http://www.example.com/success',
            'merchant-account-id' => ['value' => 'B612'],
        ]];

        $redirect = new Redirect('http://www.example.com/success', 'http://www.example.com/cancel');

        $payPalTransaction = new PayPalTransaction();
        $payPalTransaction->setNotificationUrl(self::EXAMPLE_URL);
        $payPalTransaction->setRedirect($redirect);
        $payPalTransaction->setAmount(new Money(24, 'EUR'));
        $result = $this->mapper->map($payPalTransaction, Operation::PAY, null);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testSslCreditCardTransactionWithTokenId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'ip-address' => 'test IP',
            'transaction-type' => 'authorization',
            'card-token' => [
                'token-id' => '21'
            ],
            'merchant-account-id' => ['value' => 'B612'],
        ]];

        $cardData = new CreditCardTransaction();
        $cardData->setTokenId('21');
        $cardData->setAmount(new Money(24, 'EUR'));

        $result = $this->mapper->map($cardData, Operation::RESERVE, null);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testSslCreditCardTransactionWithParentTransactionId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent5',
            'ip-address' => 'test IP',
            'transaction-type' => 'referenced-authorization',
            'merchant-account-id' => ['value' => 'B612'],
        ]];

        $transaction = new CreditCardTransaction();
        $transaction->setAmount(new Money(24, 'EUR'));
        $transaction->setParentTransactionId('parent5');
        $result = $this->mapper->map($transaction, Operation::RESERVE, Transaction::TYPE_AUTHORIZATION);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testSslCreditCardTransactionWithoutTokenIdAndParentTransactionId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $transaction = new CreditCardTransaction();
        $transaction->setAmount(new Money(24, 'EUR'));
        $this->mapper->map($transaction, Operation::RESERVE, null);
    }

    public function testSslCreditCardTransactionWithBothTokenIdAndParentTransactionId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent5',
            'ip-address' => 'test IP',
            'transaction-type' => 'referenced-authorization',
            'card-token' => [
                'token-id' => '33'
            ],
            'merchant-account-id' => ['value' => 'B612'],
        ]];

        $cardData = new CreditCardTransaction();
        $cardData->setTokenId('33');
        $cardData->setAmount(new Money(24, 'EUR'));
        $cardData->setParentTransactionId('parent5');

        $result = $this->mapper->map($cardData, Operation::RESERVE, Transaction::TYPE_AUTHORIZATION);

        $this->assertEquals(json_encode($expectedResult), $result);
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
     * @dataProvider threeDProvider
     */
    public function testThreeDCreditCardTransaction($operation, $parentTransactionType, $expectedType)
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent54',
            'ip-address' => 'test IP',
            'transaction-type' => $expectedType,
            'card-token' => [
                'token-id' => '21'
            ],
            'merchant-account-id' => ['value' => 'B612'],
        ]];

        $money = new Money(24, 'EUR');
        $creditCardTransaction = new ThreeDCreditCardTransaction();
        $creditCardTransaction->setTokenId('21');
        $creditCardTransaction->setTermUrl('https://example.com/r');
        $creditCardTransaction->setAmount($money);
        $creditCardTransaction->setParentTransactionId('parent54');

        $result = $this->mapper->map($creditCardTransaction, $operation, $parentTransactionType);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testThreeDCreditCardTransactionThrowsUnsupportedOperationException()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $money = new Money(24, 'EUR');
        $creditCardTransaction = new ThreeDCreditCardTransaction();
        $creditCardTransaction->setTokenId('21');
        $creditCardTransaction->setTermUrl('https://example.com/r');
        $creditCardTransaction->setAmount($money);
        $creditCardTransaction->setParentTransactionId('parent54');

        $this->mapper->map($creditCardTransaction, 'test', null);
    }

    public function testCancelProvider()
    {
        return [
            [
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_VOID_AUTHORIZATION
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
        $followupTransaction = new CreditCardTransaction();
        $followupTransaction->setParentTransactionId('642');
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $this->mapper->map($followupTransaction, Operation::CANCEL, $transactionType);

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => $cancelType,
            'merchant-account-id' => ['value' => 'B612'],
        ]];
        $this->assertEquals(json_encode($expectedResult), $result);
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
        $followupTransaction = new CreditCardTransaction();
        $followupTransaction->setParentTransactionId('642');
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $this->mapper->map($followupTransaction, Operation::PAY, $transactionType);

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => $payType,
            'merchant-account-id' => ['value' => 'B612'],
        ]];
        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testPay3dProvider()
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
                ThreeDCreditCardTransaction::TYPE_CHECK_ENROLLMENT,
                CreditCardTransaction::TYPE_PURCHASE
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
        $followupTransaction = new ThreeDCreditCardTransaction();
        $followupTransaction->setParentTransactionId('642');
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $this->mapper->map($followupTransaction, Operation::PAY, $transactionType);

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => $payType,
            'merchant-account-id' => ['value' => 'B612'],
        ]];
        $this->assertEquals(json_encode($expectedResult), $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testCancelInvalidParentTransaction()
    {
        $followupTransaction = new CreditCardTransaction();
        $followupTransaction->setParentTransactionId('642');
        $_SERVER['REMOTE_ADDR'] = 'test';

        $this->mapper->map($followupTransaction, Operation::CANCEL, 'test');
    }

    public function testCredit()
    {
        $followupTransaction = new CreditCardTransaction();
        $followupTransaction->setParentTransactionId('642');
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $this->mapper->map($followupTransaction, Operation::CREDIT, Transaction::TYPE_CREDIT);

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => 'credit',
            'merchant-account-id' => ['value' => 'B612'],
        ]];
        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testMappingWithPaymentMethodSpecificProperties()
    {
        $mapper = $this->createRequestMapper([
            'specific-id' => '42'
        ]);

        $followupTransaction = new CreditCardTransaction();
        $followupTransaction->setParentTransactionId('642');
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $mapper->map($followupTransaction, Operation::CREDIT, Transaction::TYPE_CREDIT);

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => 'credit',
            'specific-id' => '42',
            'merchant-account-id' => ['value' => 'B612']
        ]];
        $this->assertEquals(json_encode($expectedResult), $result);
    }

    /**
     * @return \Closure
     */
    private function createRequestIdGeneratorMock()
    {
        return function () {
            return '5B-dummy-id';
        };
    }

    /**
     * @param array $paymentMethodSpecificProperties
     * @return RequestMapper
     */
    private function createRequestMapper($paymentMethodSpecificProperties = [])
    {
        $paymentMethodConfig = $this->createMock(PaymentMethodConfig::class);
        $paymentMethodConfig->method('getMerchantAccountId')->willReturn(self::MAID);
        $paymentMethodConfig->method('mappedProperties')->willReturn($paymentMethodSpecificProperties);
        $paymentMethodConfigs = $this->createMock(PaymentMethodConfigCollection::class);
        $paymentMethodConfigs->method('get')->willReturn($paymentMethodConfig);

        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', $paymentMethodConfigs);
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        return new RequestMapper($config, $requestIdGeneratorMock);
    }
}
