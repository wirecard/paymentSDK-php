<?php

namespace WirecardTest\PaymentSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\InteractionResponse;
use Wirecard\PaymentSdk\MalformedResponseException;
use Wirecard\PaymentSdk\StatusCollection;
use Wirecard\PaymentSdk\TransactionService;

class TransactionServiceUTest extends \PHPUnit_Framework_TestCase
{
    const HANDLER = 'handler';
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

        $service = new TransactionService(
            $this->config,
            $logger,
            $httpClient,
            $requestMapper,
            $responseMapper,
            $requestIdGenerator
        );

        $this->assertAttributeEquals($this->config, 'config', $service);
        $this->assertAttributeEquals($logger, 'logger', $service);
        $this->assertAttributeEquals($requestMapper, 'requestMapper', $service);
        $this->assertAttributeEquals($responseMapper, 'responseMapper', $service);
        $this->assertAttributeEquals($requestIdGenerator, 'requestIdGenerator', $service);
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
        $client = new Client([self::HANDLER => $handler, 'http_errors' => false]);

        $service = new TransactionService($this->config, null, $client);

        $this->assertInstanceOf($class, $service->pay($this->getTransactionMock()));
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

    public function testPayProvider()
    {
        return [
            [
                new Response(
                    200,
                    [],
                    '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>myid</transaction-id>
                        <statuses>
                            <status code="200" description="test: payment OK" severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal" url="http://www.example.com" />
                        </payment-methods>
                    </payment>'
                ),
                '\Wirecard\PaymentSdk\InteractionResponse'
            ],
            [
                new Response(
                    400,
                    [],
                    '<payment>
                        <transaction-state>failure</transaction-state>
                        <statuses>
                            <status code="42" description="my test" severity="information"/>
                        </statuses>
                    </payment>'
                ),
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
        $client = new Client([self::HANDLER => $handler]);

        $this->instance = new TransactionService($this->config, null, $client);

        $this->instance->pay($this->getTransactionMock());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\MalformedResponseException
     */
    public function testPayMalformedResponseException()
    {
        $mock = new MockHandler([
            new Response(
                200,
                [],
                '<payment><transaction-state>success</transaction-state></payment>'
            )
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client([self::HANDLER => $handler]);

        $this->instance = new TransactionService($this->config, null, $client);

        $this->instance->pay($this->getTransactionMock());
    }

    public function testHandleNotificationHappyPath()
    {
        $validXmlContent = '<xml><payment></payment></xml>';

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\ResponseMapper');
        $interactionResponse = new InteractionResponse('dummy', new StatusCollection(), 'x', 'y');
        $responseMapper->method('map')->with($validXmlContent)->willReturn($interactionResponse);

        $this->instance = new TransactionService($this->config, null, null, null, $responseMapper);

        $result = $this->instance->handleNotification($validXmlContent);

        $this->assertEquals($interactionResponse, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\MalformedResponseException
     */
    public function testHandleNotificationMalformedResponseException()
    {
        $invalidXmlContent = '<xml><payment></payment></xml>';

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\ResponseMapper');
        $responseMapper->method('map')->with($invalidXmlContent)->willThrowException(new MalformedResponseException());

        $this->instance = new TransactionService($this->config, null, null, null, $responseMapper);

        $this->instance->handleNotification($invalidXmlContent);
    }

    public function testHandleResponseHappyPath()
    {
        $validContent = [
            'eppresponse' => base64_encode('<xml><payment></payment></xml>')
        ];

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\ResponseMapper');
        $interactionResponse = new InteractionResponse('dummy', new StatusCollection(), 'x', 'y');
        $responseMapper->method('map')->with($validContent['eppresponse'])->willReturn($interactionResponse);

        $this->instance = new TransactionService($this->config, null, null, null, $responseMapper);

        $result = $this->instance->handleResponse($validContent);

        $this->assertEquals($interactionResponse, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\MalformedResponseException
     */
    public function testHandleResponseMalformedResponseException()
    {
        $invalidXmlContent = [];

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\ResponseMapper');

        $this->instance = new TransactionService($this->config, null, null, null, $responseMapper);

        $this->instance->handleResponse($invalidXmlContent);
    }
}
