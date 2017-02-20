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

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\CreditCardTransaction;
use Wirecard\PaymentSdk\FollowupTransaction;
use Wirecard\PaymentSdk\Money;
use Wirecard\PaymentSdk\PayPalTransaction;
use Wirecard\PaymentSdk\Redirect;
use Wirecard\PaymentSdk\ThreeDAuthorizationTransaction;
use Wirecard\PaymentSdk\RequestMapper;
use Wirecard\PaymentSdk\ThreeDCreditCardTransaction;

class RequestMapperUTest extends \PHPUnit_Framework_TestCase
{
    const MAID = 'B612';

    const EXAMPLE_URL = 'http://www.example.com';

    public function testPayPalTransaction()
    {
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createMock('Wirecard\PaymentSdk\RequestIdGenerator');
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $requestIdGeneratorMock->method('generate')
            ->willReturn('5B-dummy-id');

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'transaction-type' => 'debit',
            'payment-methods' => ['payment-method' => [['name' => 'paypal']]],
            'cancel-redirect-url' => 'http://www.example.com/cancel',
            'success-redirect-url' => 'http://www.example.com/success',
            'notifications' => ['notification' => [['url' => self::EXAMPLE_URL]]]
        ]];

        $redirect = new Redirect('http://www.example.com/success', 'http://www.example.com/cancel');
        $transaction = new PayPalTransaction(new Money(24, 'EUR'), self::EXAMPLE_URL, $redirect);
        $result = $mapper->map($transaction);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testSslCreditCardTransactionWithTokenId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createMock('Wirecard\PaymentSdk\RequestIdGenerator');
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $requestIdGeneratorMock->method('generate')
            ->willReturn('5B-dummy-id');

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'transaction-type' => 'authorization',
            'card-token' => [
                'token-id' => '21'
            ],
            'ip-address' => 'test IP'
        ]];

        $transaction = new CreditCardTransaction(new Money(24, 'EUR'));
        $transaction->setTokenId('21');
        $result = $mapper->map($transaction);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testThreeDCreditCardTransaction()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createMock('Wirecard\PaymentSdk\RequestIdGenerator');
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $requestIdGeneratorMock->method('generate')
            ->willReturn('5B-dummy-id');

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'transaction-type' => 'check-enrollment',
            'card-token' => [
                'token-id' => '21'
            ],
            'ip-address' => 'test IP'
        ]];

        $money = new Money(24, 'EUR');
        $transaction = new ThreeDCreditCardTransaction($money, '21', 'https://example.com/n', 'https://example.com/r');
        $result = $mapper->map($transaction);

        $this->assertEquals(json_encode($expectedResult), $result);
    }

    public function testThreeDAuthorizationTransaction()
    {
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createMock('Wirecard\PaymentSdk\RequestIdGenerator');
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $requestIdGeneratorMock->method('generate')
            ->willReturn('5B-dummy-id');

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
        $requestIdGeneratorMock = $this->createMock('Wirecard\PaymentSdk\RequestIdGenerator');
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);
        $followupTransaction = new FollowupTransaction('642');

        $requestIdGeneratorMock->method('generate')
            ->willReturn('5B-dummy-id');

        $result = $mapper->map($followupTransaction);

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'transaction-type' => 'void-authorization',
            'parent-transaction-id' => '642',

        ]];
        $this->assertEquals(json_encode($expectedResult), $result);
    }
}
