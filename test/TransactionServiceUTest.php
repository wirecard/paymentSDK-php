<?php

namespace WirecardTest\PaymentSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\TransactionService;

class TransactionServiceUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionService
     */
    private $instance;

    /**
     * @var Config
     */
    private $config;

    public function setUp()
    {
        $this->config = $this->createMock('\Wirecard\PaymentSdk\Config');
        $this->config->method('getHttpUser')->willReturn('abc123');
        $this->config->method('getHttpPassword')->willReturn('password');
        $this->config->method('getMerchantAccountId')->willReturn('maid');
        $this->config->method('getUrl')->willReturn('http://engine.ok');

        $this->instance = new TransactionService($this->config);
    }

    public function testFullConstructor()
    {
        $logger = $this->createMock('\Monolog\Logger');
        $httpClient = $this->createMock('\GuzzleHttp\Client');
        $requestMapper = $this->createMock('\Wirecard\PaymentSdk\RequestMapper');
        $responseMapper = $this->createMock('\Wirecard\PaymentSdk\ResponseMapper');
        $requestIdGenerator = $this->createMock('\Wirecard\PaymentSdk\RequestIdGenerator');

        $instance = new TransactionService($this->config, $logger, $httpClient, $requestMapper, $responseMapper,
            $requestIdGenerator);

        $this->assertAttributeEquals($this->config, 'config', $instance);
        $this->assertAttributeEquals($logger, 'logger', $instance);
        $this->assertAttributeEquals($requestMapper, 'requestMapper', $instance);
        $this->assertAttributeEquals($responseMapper, 'responseMapper', $instance);
        $this->assertAttributeEquals($requestIdGenerator, 'requestIdGenerator', $instance);
    }

    public function testGetConfig()
    {
        $helper = function () {
            return $this->getConfig();
        };

        $method = $helper->bindTo($this->instance, $this->instance);

        $this->assertEquals($this->config, $method());
    }

    public function testGetLogger()
    {
        $helper = function () {
            return $this->getLogger();
        };

        $method = $helper->bindTo($this->instance, $this->instance);

        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $method());
    }

    public function testGetHttpClient()
    {
        $helper = function () {
            return $this->getHttpClient();
        };

        $method = $helper->bindTo($this->instance, $this->instance);

        $this->assertInstanceOf('\GuzzleHttp\Client', $method());
    }

    public function testGetRequestMapper()
    {
        $helper = function () {
            return $this->getRequestMapper();
        };

        $method = $helper->bindTo($this->instance, $this->instance);

        $this->assertInstanceOf('\Wirecard\PaymentSdk\RequestMapper', $method());
    }

    public function testGetResponseMapper()
    {
        $helper = function () {
            return $this->getResponseMapper();
        };

        $method = $helper->bindTo($this->instance, $this->instance);

        $this->assertInstanceOf('\Wirecard\PaymentSdk\ResponseMapper', $method());
    }

    public function testGetRequestIdGenerator()
    {
        $helper = function () {
            return $this->getRequestIdGenerator();
        };

        $method = $helper->bindTo($this->instance, $this->instance);

        $this->assertInstanceOf('\Wirecard\PaymentSdk\RequestIdGenerator', $method());
    }

    /**
     * @param $response
     * @param $class
     * @dataProvider testPayProvider
     */
    public function testPay($response, $class)
    {
        $mock = new MockHandler([
            $response
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler, 'http_errors' => false]);

        $instance = new TransactionService($this->config, null, $client);

        $this->assertInstanceOf($class, $instance->pay($this->getTransactionMock()));
    }

    public function testPayProvider()
    {
        return [
            [
                new Response(200, [], json_encode([
                    'payment' => [
                        'transaction-state' => 'success',
                        'transaction-id' => 'myid',
                        'payment-methods' => [
                            'payment-method' => [
                                ['url' => 'http://paypal.test']
                            ]
                        ]
                    ]
                ])),
                '\Wirecard\PaymentSdk\InteractionResponse'
            ],
            [
                new Response(400, [], json_encode([
                    'payment' => [
                        'transaction-state' => 'failure'
                    ]
                ])),
                '\Wirecard\PaymentSdk\FailureResponse'
            ]
        ];
    }

    /**
     * @expectedException \GuzzleHttp\Exception\RequestException
     */
    public function testPayRequestException()
    {
        $mock = new MockHandler([
            new Response(500, [], json_encode([
                'payment' => [
                    'transaction-state' => 'success',
                ]
            ]))
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->instance = new TransactionService($this->config, null, $client);

        $this->instance->pay($this->getTransactionMock());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\MalformedResponseException
     */
    public function testPayMalformedResponseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'payment' => [
                    'transaction-state' => 'success',
                ]
            ]))
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->instance = new TransactionService($this->config, null, $client);

        $this->instance->pay($this->getTransactionMock());
    }

    protected function getTransactionMock()
    {
        $transaction = $this->createMock('\Wirecard\PaymentSdk\PayPalTransaction');

        $money = $this->createMock('\Wirecard\PaymentSdk\Money');
        $money->method('getAmount')->willReturn(20.23);
        $money->method('getCurrency')->willReturn('EUR');

        $transaction->method('getAmount')->willReturn($money);

        $redirect = $this->createMock('\Wirecard\PaymentSdk\Redirect');
        $redirect->method('getSuccessUrl')->willReturn('http://www.example.com/success');
        $redirect->method('getCancelUrl')->willReturn('http://www.example.com/cancel');

        $transaction->method('getRedirect')->willReturn($redirect);

        return $transaction;
    }
}
