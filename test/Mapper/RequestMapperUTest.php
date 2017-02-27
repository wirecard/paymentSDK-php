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

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\ThreeDAuthorizationTransaction;
use Wirecard\PaymentSdk\Mapper\RequestMapper;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;

class RequestMapperUTest extends \PHPUnit_Framework_TestCase
{
    const MAID = 'B612';

    const EXAMPLE_URL = 'http://www.example.com';

    public function testPayPalTransaction()
    {
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'notifications' => ['notification' => [['url' => self::EXAMPLE_URL]]],
            'transaction-type' => 'debit',
            'payment-methods' => ['payment-method' => [['name' => 'paypal']]],
            'cancel-redirect-url' => 'http://www.example.com/cancel',
            'success-redirect-url' => 'http://www.example.com/success',
        ]];

        $redirect = new Redirect('http://www.example.com/success', 'http://www.example.com/cancel');

        $payPalTransaction = new PayPalTransaction();
        $payPalTransaction->setNotificationUrl(self::EXAMPLE_URL);
        $payPalTransaction->setRedirect($redirect);
        $payPalTransaction->setAmount(new Money(24, 'EUR'));
        $result = $mapper->map($payPalTransaction, Operation::PAY);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testSslCreditCardTransactionWithTokenId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'ip-address' => 'test IP',
            'transaction-type' => 'authorization',
            'card-token' => [
                'token-id' => '21'
            ],
        ]];

        $cardData = new CreditCardTransaction();
        $cardData->setTokenId('21');
        $cardData->setAmount(new Money(24, 'EUR'));

        $result = $mapper->map($cardData, Operation::RESERVE);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testSslCreditCardTransactionWithParentTransactionId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent5',
            'ip-address' => 'test IP',
            'transaction-type' => 'referenced-authorization',
        ]];

        $transaction = new CreditCardTransaction();
        $transaction->setAmount(new Money(24, 'EUR'));
        $transaction->setParentTransactionId('parent5');
        $result = $mapper->map($transaction, Operation::RESERVE);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testSslCreditCardTransactionWithoutTokenIdAndParentTransactionId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $transaction = new CreditCardTransaction();
        $transaction->setAmount(new Money(24, 'EUR'));
        $mapper->map($transaction);
    }

    public function testSslCreditCardTransactionWithBothTokenIdAndParentTransactionId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent5',
            'ip-address' => 'test IP',
            'transaction-type' => 'referenced-authorization',
            'card-token' => [
                'token-id' => '33'
            ]
        ]];

        $cardData = new CreditCardTransaction();
        $cardData->setTokenId('33');
        $cardData->setAmount(new Money(24, 'EUR'));
        $cardData->setParentTransactionId('parent5');

        $result = $mapper->map($cardData, Operation::RESERVE);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testThreeDCreditCardTransaction()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'ip-address' => 'test IP',
            'transaction-type' => 'check-enrollment',
            'card-token' => [
                'token-id' => '21'
            ],
        ]];

        $money = new Money(24, 'EUR');
        $creditCardTransaction = new ThreeDCreditCardTransaction();
        $creditCardTransaction->setTokenId('21');
        $creditCardTransaction->setTermUrl('https://example.com/r');
        $creditCardTransaction->setAmount($money);

        $result = $mapper->map($creditCardTransaction, Operation::RESERVE);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testThreeDAuthorizationTransaction()
    {
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $payload = [
            'PaRes' => 'sth',
            'MD' => base64_encode(json_encode([
                'enrollment-check-transaction-id' => '642'
            ]))
        ];

        $refTransaction = new ThreeDAuthorizationTransaction($payload);
        $result = $mapper->map($refTransaction);

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'transaction-type' => 'authorization',
            'parent-transaction-id' => '642',
            'three-d' => [
                'pares' => 'sth'
            ]
        ]];

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testCancel()
    {
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);
        $followupTransaction = new CreditCardTransaction();
        $followupTransaction->setParentTransactionId('642');
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $mapper->map($followupTransaction, Operation::CANCEL);

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => 'void-authorization',

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
}
