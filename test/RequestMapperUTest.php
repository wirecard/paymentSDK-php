<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Money;
use Wirecard\PaymentSdk\PayPalTransaction;
use Wirecard\PaymentSdk\RequestIdGenerator;
use Wirecard\PaymentSdk\RequestMapper;

class RequestMapperUTest extends \PHPUnit_Framework_TestCase
{
    const MAID = 'B612';
    /**
     * @var RequestMapper
     */
    private $mapper;

    /**
     * @var RequestIdGenerator
     */
    private $requestIdGeneratorMock;

    public function setUp()
    {
        $config = new Config('dummyUser', 'dummyPassword', self::MAID, 'secret');
        $this->requestIdGeneratorMock = $this->createMock('Wirecard\PaymentSdk\RequestIdGenerator');
        $this->mapper = new RequestMapper($config, $this->requestIdGeneratorMock);
    }

    public function testSample()
    {
        $this->requestIdGeneratorMock->method('generate')
            ->willReturn('5B-dummy-id');

        $expectedResult = ['payment' => [
            "merchant-account-id" => "B612",
            "request-id" => "5B-dummy-id",
            "transaction-type" => "debit",
            "payment-methods" => [["payment-method" => ["name" => "paypal"]]],
            "requested-amount" => ["currency" => "EUR", "value" => 24]
        ]];

        $transaction = new PayPalTransaction(new Money(24, 'EUR'), 'http://www.example.com');
        $result = $this->mapper->map($transaction);

        $this->assertEquals(json_encode($expectedResult), $result);

        /*
         * expected result (as JSON):
        {
        "payment":
        {"merchant-account-id":"B612",
        "request-id":"5B-dummy-id","
        transaction-type":"debit",
        "payment-methods":[{"payment-method":{"name":"paypal"}}],
        "requested-amount":{"currency":"EUR","value":24}
        }}
        */
    }
}
