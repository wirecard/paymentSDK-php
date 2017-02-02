<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Money;
use Wirecard\PaymentSdk\PayPalTransaction;
use Wirecard\PaymentSdk\Redirect;
use Wirecard\PaymentSdk\RequestMapper;

class RequestMapperUTest extends \PHPUnit_Framework_TestCase
{
    const MAID = 'B612';

    const EXAMPLE_URL = 'http://www.example.com';

    public function testRedirectInfoInTransaction()
    {
        $config = new Config(self::EXAMPLE_URL, 'dummyUser', 'dummyPassword', self::MAID, 'secret');
        $requestIdGeneratorMock = $this->createMock('Wirecard\PaymentSdk\RequestIdGenerator');
        $mapper = new RequestMapper($config, $requestIdGeneratorMock);

        $requestIdGeneratorMock->method('generate')
            ->willReturn('5B-dummy-id');

        $expectedResult = ['payment' => [
            'merchant-account-id' => ['value' => 'B612'],
            'request-id' => '5B-dummy-id',
            'transaction-type' => 'debit',
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
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
}
