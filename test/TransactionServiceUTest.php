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

namespace WirecardTest\PaymentSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Class TransactionServiceUTest
 * @package WirecardTest\PaymentSdk
 * @method getLogger
 * @method getHttpClient
 * @method getRequestMapper
 * @method getResponseMapper
 * @method getRequestIdGenerator
 */
class TransactionServiceUTest extends \PHPUnit_Framework_TestCase
{
    const HANDLER = 'handler';
    const MAID = '213asdf';

    /**
     * @var TransactionService
     */
    private $instance;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $shopData;

    public function setUp()
    {
        $paymentMethodConfig = $this->createMock(PaymentMethodConfig::class);
        $paymentMethodConfig->method('getMerchantAccountId')->willReturn(self::MAID);
        $paymentMethodConfig->method('mappedProperties')->willReturn([]);

        $this->shopData = array(
            'shop-system-name' => 'paymentSDK',
            'shop-system-version' => '1.0',
            'plugin-name' => 'plugin',
            'plugin-version' => '1.1'
        );


        $this->config = $this->createMock('\Wirecard\PaymentSdk\Config\Config');
        $this->config->method('getHttpUser')->willReturn('abc123');
        $this->config->method('getHttpPassword')->willReturn('password');
        $this->config->method('get')->willReturn($paymentMethodConfig);
        $this->config->method('getBaseUrl')->willReturn('http://engine.ok');
        $this->config->method('getDefaultCurrency')->willReturn('EUR');
        $this->config->method('getLogLevel')->willReturn(Logger::ERROR);
        $this->config->method('getShopHeader')->willReturn(array('headers' => $this->shopData));
        $this->instance = new TransactionService($this->config);
    }

    public function testFullConstructor()
    {
        $logger = $this->createMock('\Monolog\Logger');
        $httpClient = $this->createMock('\GuzzleHttp\Client');
        $requestMapper = $this->createMock('\Wirecard\PaymentSdk\Mapper\RequestMapper');
        $responseMapper = $this->createMock('\Wirecard\PaymentSdk\Mapper\ResponseMapper');
        $requestIdGenerator = function () {
            return 42;
        };

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

    public function testGetLogger()
    {
        $helper = function () {
            return $this->getLogger();
        };

        $method = $helper->bindTo($this->instance, $this->instance);

        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $method());
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

        $this->assertInstanceOf($class, $service->pay($this->getTestPayPalTransaction()));
    }

    public function testGetDataForUpi()
    {

        $data = new \stdClass();
        $data->request_time_stamp = gmdate('YmdHis');
        $data->transaction_type = "authorization-only";
        $data->merchant_account_id = self::MAID;
        $data->requested_amount = 0;
        $data->requested_amount_currency = "EUR";
        $data->locale = "en";
        $data->payment_method = CreditCardTransaction::NAME;

        $instanceData = json_decode($this->instance->getDataForUpiUi("en"));
        unset($instanceData->request_id);
        unset($instanceData->request_signature);

        $this->assertEquals($data, $instanceData);
    }

    public function testCheckCredentialsHandlesException()
    {
        $mock = new MockHandler([
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client([self::HANDLER => $handler, 'http_errors' => false]);

        $logger = $this->createMock(LoggerInterface::class);
        $service = new TransactionService($this->config, $logger, $client);

        $this->assertFalse($service->checkCredentials());
    }

    public function testReserveCreditCardTransaction()
    {
        $globalConfig = new Config('http://api-test.wirecard.com', 'test', 'pass', 'EUR');
        $config = new CreditCardConfig('maid', 'secret');
        $config->addThreeDMinLimit(new Amount(50.0, 'EUR'));
        $globalConfig->add($config);

        $transaction = new CreditCardTransaction();
        $transaction->setAmount(new Amount(70.0, 'EUR'));

        //prepare RequestMapper
        $mappedRequest = '{"mocked": "json", "response": "object"}';
        $requestMapper = $this->createMock('\Wirecard\PaymentSdk\Mapper\RequestMapper');
        $requestMapper->expects($this->once())
            ->method('map')
            ->with($this->equalTo($transaction))
            ->willReturn($mappedRequest);

        //prepare Guzzle
        $guzzleMock = new MockHandler([
            new Response(200, [], '<payment>
                                                <transaction-state>success</transaction-state>
                                                <transaction-type>debit</transaction-type>
                                                <transaction-id>myid</transaction-id>
                                                <request-id>123</request-id>
                                                <payment-methods>
                                                    <payment-method name="creditcard" />
                                                </payment-methods>
                                                <statuses>
                                                    <status code="200.000" description="ok" severity="info"/>
                                                </statuses>
                                              </payment>')
        ]);
        $handler = HandlerStack::create($guzzleMock);
        $client = new Client([self::HANDLER => $handler, 'http_errors' => false]);

        $logger = $this->createMock(LoggerInterface::class);

        $service = new TransactionService($globalConfig, $logger, $client, $requestMapper);
        $this->assertInstanceOf(SuccessResponse::class, $service->reserve($transaction));
    }

    public function testReserveCreditCardWithFallback()
    {
        $globalConfig = new Config('http://api-test.wirecard.com', 'test', 'pass', 'EUR');
        $config = new CreditCardConfig('maid', 'secret');
        $config->addThreeDMinLimit(new Amount(50.0, 'EUR'));
        $globalConfig->add($config);

        $transaction = new CreditCardTransaction();
        $transaction->setAmount(new Amount(70.0, 'EUR'));

        //prepare RequestMapper
        $mappedRequest = '{"mocked": "json", "response": "object"}';
        $requestMapper = $this->createMock('\Wirecard\PaymentSdk\Mapper\RequestMapper');
        $requestMapper->method('map')
            ->with($this->equalTo($transaction))
            ->willReturn($mappedRequest);

        //prepare Guzzle
        $responseToMap = '<payment>
                            <transaction-state>failure</transaction-state>
                            <transaction-type>debit</transaction-type>
                            <transaction-id>myid</transaction-id>
                            <request-id>123</request-id>
                            <payment-methods>
                                <payment-method name="creditcard" />
                            </payment-methods>
                            <statuses>
                             <status code="500.1072" description="error check enrollment" severity="error"/>
                            </statuses>
                          </payment>';
        $responseToMap2 = '<payment>
                            <transaction-state>success</transaction-state>
                            <transaction-type>debit</transaction-type>
                            <transaction-id>myid</transaction-id>
                            <request-id>123</request-id>
                            <payment-methods>
                                <payment-method name="creditcard" />
                            </payment-methods>
                            <statuses>
                                <status code="200" description="test: payment OK" severity="information"/>
                            </statuses>
                          </payment>';
        $guzzleMock = new MockHandler([
            new Response(200, [], $responseToMap),
            new Response(200, [], $responseToMap2)
        ]);
        $handler = HandlerStack::create($guzzleMock);
        $client = new Client([self::HANDLER => $handler, 'http_errors' => false]);

        $logger = $this->createMock(LoggerInterface::class);
        $service = new TransactionService($globalConfig, $logger, $client, $requestMapper);
        $this->assertInstanceOf(SuccessResponse::class, $service->reserve($transaction));
    }

    public function testGetParentTransactionDetails()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setParentTransactionId('myparentid');
        $transaction->setParentTransactionType('credit');
        $transaction->setOperation('reserve');
        //prepare RequestMapper
        $mappedRequest = '{"mocked": "json", "response": "object"}';
        $requestMapper = $this->createMock('\Wirecard\PaymentSdk\Mapper\RequestMapper');
        $requestMapper->expects($this->once())
            ->method('map')
            ->with($this->equalTo($transaction))
            ->willReturn($mappedRequest);

        //prepare Guzzle
        $guzzleMock = new MockHandler([
            new Response(200, [], '{"payment":{"transaction-type":"credit"}}'),
            new Response(200, [], '<payment><xml-response></xml-response></payment>')
        ]);
        $handler = HandlerStack::create($guzzleMock);
        $client = new Client([self::HANDLER => $handler, 'http_errors' => false]);

        //prepare ResponseMapper
        $responseMapper = $this->createMock('\Wirecard\PaymentSdk\Mapper\ResponseMapper');
        $response = $this->createMock('\Wirecard\PaymentSdk\Response\Response');
        $responseMapper
            ->method('map')
            ->willReturn($response);

        $service = new TransactionService($this->config, null, $client, $requestMapper, $responseMapper);
        $service->reserve($transaction);
    }

    protected function getTestPayPalTransaction()
    {
        $redirect = new Redirect('http://www.example.com/success', 'http://www.example.com/cancel');
        $payPalTransaction = new PayPalTransaction();
        $payPalTransaction->setNotificationUrl('notUrl');
        $payPalTransaction->setRedirect($redirect);
        $payPalTransaction->setAmount(new Amount(20.23, 'EUR'));

        return $payPalTransaction;
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
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status code="200" description="test: payment OK" severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal" url="http://www.example.com" />
                        </payment-methods>
                    </payment>'
                ),
                '\Wirecard\PaymentSdk\Response\InteractionResponse'
            ],
            [
                new Response(
                    400,
                    [],
                    '<payment>
                        <transaction-state>failure</transaction-state>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status code="42" description="my test" severity="information"/>
                        </statuses>
                    </payment>'
                ),
                '\Wirecard\PaymentSdk\Response\FailureResponse'
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

        $this->instance->pay($this->getTestPayPalTransaction());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
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

        $this->instance->pay($this->getTestPayPalTransaction());
    }

    public function testHandleNotificationHappyPath()
    {
        $validXmlContent = '<payment>
                    <transaction-type>none</transaction-type>
                    <request-id>1</request-id>
                    <transaction-id>2</transaction-id>
                    <statuses><status code="1" description="a" severity="0"></status></statuses>
                </payment>';

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\ResponseMapper');
        $interactionResponse = new InteractionResponse(simplexml_load_string($validXmlContent), 'http://y.z');
        $responseMapper->method('mapInclSignature')->with($validXmlContent)->willReturn($interactionResponse);

        $this->instance = new TransactionService($this->config, null, null, null, $responseMapper);

        $result = $this->instance->handleNotification($validXmlContent);

        $this->assertEquals($interactionResponse, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testHandleNotificationMalformedResponseException()
    {
        $invalidXmlContent = '<xml><payment></payment></xml>';

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\ResponseMapper');
        $responseMapper
            ->method('mapInclSignature')
            ->with($invalidXmlContent)
            ->willThrowException(new MalformedResponseException());

        $this->instance = new TransactionService($this->config, null, null, null, $responseMapper);

        $this->instance->handleNotification($invalidXmlContent);
    }

    public function testHandle3DResponseHappyPath()
    {
        $validContent = [
            'eppresponse' => base64_encode('<xml>
                    <payment></payment>
                    <transaction-type>none</transaction-type>
                    <request-id>1</request-id>
                    <transaction-id>2</transaction-id>
                    <statuses><status code="1" description="a" severity="0"></status></statuses>
                </xml>')
        ];
        $simpleXml = simplexml_load_string(base64_decode($validContent['eppresponse']));

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\ResponseMapper');
        $interactionResponse = new InteractionResponse($simpleXml, 'http://y.z');
        $responseMapper
            ->method('mapInclSignature')
            ->with($validContent['eppresponse'])
            ->willReturn($interactionResponse);

        $this->instance = new TransactionService($this->config, null, null, null, $responseMapper);

        $result = $this->instance->handleResponse($validContent);

        $this->assertEquals($interactionResponse, $result);
    }

    public function testHandleResponseIdealHappyPath()
    {
        $validContent = [
            'ec' => '123',
            'trxid' => '456',
            'request_id' => '789'
        ];

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\ResponseMapper');
        $response = $this->createMock('Wirecard\PaymentSdk\Response\SuccessResponse');
        $responseMapper->method('map')->willReturn($response);

        $httpClient = $this->createMock('GuzzleHttp\Client');
        $httpResponse = $this->createMock('Psr\Http\Message\ResponseInterface');
        $streamInterface = $this->createMock('Psr\Http\Message\StreamInterface');
        $streamInterface->method('getContents')->willReturn('<xml></xml>');
        $httpResponse->method('getBody')->willReturn($streamInterface);
        $httpClient->method('request')->willReturn($httpResponse);

        $this->instance = new TransactionService($this->config, null, $httpClient, null, $responseMapper);

        $result = $this->instance->handleResponse($validContent);

        $this->assertEquals($response, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testHandleResponseMalformedResponseException()
    {
        $invalidXmlContent = [];

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\ResponseMapper');

        $this->instance = new TransactionService($this->config, null, null, null, $responseMapper);

        $this->instance->handleResponse($invalidXmlContent);
    }


    public function testGetDataForCreditCardUi()
    {
        $requestIdGenerator = function () {
            return 'abc123';
        };

        $this->instance = new TransactionService($this->config, null, null, null, null, $requestIdGenerator);
        $data = json_decode($this->instance->getDataForCreditCardUi(), true);

        $this->assertArrayHasKey('request_signature', $data);
        unset($data['request_signature']);

        $this->assertArrayHasKey('request_time_stamp', $data);
        unset($data['request_time_stamp']);

        $this->assertEquals(array(
            'request_id' => 'abc123',
            'merchant_account_id' => self::MAID,
            'transaction_type' => 'authorization-only',
            'requested_amount' => 0,
            'requested_amount_currency' => $this->config->getDefaultCurrency(),
            'locale' => 'en',
            'payment_method' => 'creditcard',
        ), $data);
    }

    public function testGetDataForCreditCardMotoUi()
    {
        $requestIdGenerator = function () {
            return 'abc123';
        };

        $this->instance = new TransactionService($this->config, null, null, null, null, $requestIdGenerator);
        $data = json_decode($this->instance->getDataForCreditCardMotoUi(), true);

        $this->assertArrayHasKey('request_signature', $data);
        unset($data['request_signature']);

        $this->assertArrayHasKey('request_time_stamp', $data);
        unset($data['request_time_stamp']);

        $this->assertEquals(array(
            'request_id' => 'abc123',
            'merchant_account_id' => self::MAID,
            'transaction_type' => 'authorization-only',
            'requested_amount' => 0,
            'requested_amount_currency' => $this->config->getDefaultCurrency(),
            'locale' => 'en',
            'payment_method' => 'creditcard',
        ), $data);
    }

    public function testRatePayInvoiceDeviceIdent()
    {
        $data = $this->instance->getRatePayInvoiceDeviceIdent();
        $this->assertNotEmpty($data);
    }

    public function testRatePayInstallmentDeviceIdent()
    {
        $data = $this->instance->getRatePayInstallmentDeviceIdent();

        $this->assertNotEmpty($data);
    }

    public function testgetRatePayScript()
    {
        $deviceIdentToken = 'abc123token';
        $script = $this->instance->getRatePayScript($deviceIdentToken);

        $this->assertContains($deviceIdentToken, $script);
    }

    public function testHandleResponseThreeD()
    {
        $md = [
            'enrollment-check-transaction-id' => 'parentid',
            'operation-type' => 'authorization'
        ];

        $validContent = [
            'MD' => base64_encode(json_encode($md)),
            'PaRes' => 'arbitrary PaRes',
            'eppresponse' => 'content',

        ];

        $transaction = new CreditCardTransaction();
        $transaction->setParentTransactionId($md['enrollment-check-transaction-id']);
        $transaction->setOperation('authorization');
        $transaction->setPaRes($validContent['PaRes']);
        $transaction->setThreeD(true);
        $transaction->setConfig($this->createMock(PaymentMethodConfig::class));

        $successResponse = $this->mockProcessingRequest($transaction);
        $result = $this->instance->handleResponse($validContent);

        $this->assertEquals($successResponse, $result);
    }

    public function testHandleRatepayResponse()
    {
        $md = 'content';

        $validContent = [
            'base64payload' => $md,
            'psp_name' => 'engine_payments'
        ];

        $transaction = new RatepayInstallmentTransaction();
        $transaction->setOperation('reserve');

        $successResponse = $this->mockProcessingRequestInclSignature($transaction);

        $result = $this->instance->handleResponse($validContent);

        $this->assertEquals($successResponse, $result);
    }

    public function testHandleSyncResponse()
    {
        $validContent = [
            'sync_response' => 'content',

        ];

        $transaction = new SepaTransaction();
        $transaction->setOperation(Operation::PAY);

        $successResponse = $this->mockProcessingRequestInclSignature($transaction);

        $result = $this->instance->handleResponse($validContent);

        $this->assertEquals($successResponse, $result);
    }

    public function testCancel()
    {
        $tx = new CreditCardTransaction();
        $tx->setParentTransactionId('parent-id');

        $successResponse = $this->mockProcessingRequest($tx);

        $result = $this->instance->cancel($tx);

        $this->assertEquals(Operation::CANCEL, $result->getOperation());

        $this->assertEquals($successResponse, $result);
    }

    public function testCancelRefund()
    {
        $mock = new MockHandler([
            new Response(
                200,
                [],
                '{"payment":{"transaction-type":"capture-authorization"}}'
            ),
            new Response(
                200,
                [],
                '<payment>
<transaction-state>failure</transaction-state>
<statuses><status code="500.1057" description="a" severity="0"></status></statuses>
</payment>'
            ),
            new Response(
                200,
                [],
                '{"payment":{"transaction-type":"capture-authorization"}}'
            ),
            new Response(
                200,
                [],
                '<payment>
<transaction-type>refund-capture</transaction-type>
<transaction-id>23tghfrhfgh</transaction-id>
<request-id>afhnfgdg</request-id>
<payment-methods><payment-method name="creditcard" /></payment-methods>
<transaction-state>success</transaction-state>
<statuses><status code="200.000" description="a" severity="0"></status></statuses>
</payment>'
            ),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client([self::HANDLER => $handler]);

        $this->instance = new TransactionService($this->config, null, $client);

        $tx = new CreditCardTransaction();
        $tx->setParentTransactionId('parent-id');
        $this->assertInstanceOf(SuccessResponse::class, $this->instance->cancel($tx));
    }

    public function testCredit()
    {
        $tx = new CreditCardTransaction();
        $tx->setTokenId('token-id');

        $successResponse = $this->mockProcessingRequest($tx);

        $result = $this->instance->credit($tx);

        $this->assertEquals($successResponse, $result);
    }

    public function testRequestIdRandomness()
    {
        $bindHelper = function () {
            return $this->requestIdGenerator;
        };

        $bound = $bindHelper->bindTo($this->instance, $this->instance);

        $requestId = call_user_func($bound());
        usleep(1);
        $laterRequestId = call_user_func($bound());

        $this->assertTrue(is_string($requestId));
        $this->assertTrue(is_string($laterRequestId));
        $this->assertNotEquals($requestId, $laterRequestId);
    }

    /**
     * @param $tx
     * @return SuccessResponse
     */
    private function mockProcessingRequest($tx)
    {
        $requestMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\RequestMapper');
        $authRequestObject = "dummy_request_payload";
        $requestMapper->method('map')->with($tx)->willReturn($authRequestObject);

        $httpResponse = $this->createMock('\Psr\Http\Message\ResponseInterface');
        $client = $this->createMock('\GuzzleHttp\Client');
        $client->method('request')->willReturn($httpResponse);
        $httpResponseBody = $this->createMock('\Psr\Http\Message\StreamInterface');
        $httpResponse->method('getBody')->willReturn($httpResponseBody);
        $httpResponseContent = 'content';
        $httpResponseBody->method('getContents')->willReturn($httpResponseContent);

        $xmlResponse = '<xml>
                <transaction-id></transaction-id>
                <request-id></request-id>
                <transaction-type></transaction-type>
                <statuses><status code="1" description="a" severity="0"></status></statuses>
            </xml>';
        $successResponse = new SuccessResponse(simplexml_load_string($xmlResponse), 'y');
        $responseMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\ResponseMapper');
        $responseMapper->method('map')->with($httpResponseContent)->willReturn($successResponse);

        $this->instance = new TransactionService($this->config, null, $client, $requestMapper, $responseMapper);
        return $successResponse;
    }

    private function mockProcessingRequestInclSignature($tx)
    {
        $requestMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\RequestMapper');
        $authRequestObject = "dummy_request_payload";
        $requestMapper->method('map')->with($tx)->willReturn($authRequestObject);

        $httpResponse = $this->createMock('\Psr\Http\Message\ResponseInterface');
        $client = $this->createMock('\GuzzleHttp\Client');
        $client->method('request')->willReturn($httpResponse);
        $httpResponseBody = $this->createMock('\Psr\Http\Message\StreamInterface');
        $httpResponse->method('getBody')->willReturn($httpResponseBody);
        $httpResponseContent = 'content';
        $httpResponseBody->method('getContents')->willReturn($httpResponseContent);

        $xmlResponse = '<xml>
                <transaction-id></transaction-id>
                <request-id></request-id>
                <transaction-type></transaction-type>
                <statuses><status code="1" description="a" severity="0"></status></statuses>
            </xml>';
        $successResponse = new SuccessResponse(simplexml_load_string($xmlResponse), 'y');
        $responseMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\ResponseMapper');
        $responseMapper->method('mapInclSignature')->with($httpResponseContent)->willReturn($successResponse);

        $this->instance = new TransactionService($this->config, null, $client, $requestMapper, $responseMapper);
        return $successResponse;
    }


    public function testShopDataOnSendRequest()
    {
        $shopData = $this->shopData;
        $checkRequestForShopData = function ($callback) use ($shopData) {
            $headers = $callback['headers'];
            $intersect = array_intersect($headers, $shopData);
            return empty(array_diff($intersect, $shopData));
        };

        $transaction = new PayPalTransaction();
        $transaction->setParentTransactionId('1');
        $client = $this->createMock('\GuzzleHttp\Client');

        $streamInterface = $this->createMock('Psr\Http\Message\StreamInterface');
        $streamInterface->method('getContents')->willReturn(null);
        $httpResponse = $this->createMock('Psr\Http\Message\ResponseInterface');
        $httpResponse->method('getBody')->willReturn($streamInterface);

        $requestMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\RequestMapper');
        $requestMapper->method('map')->willReturn(null);

        $responseMapper = $this->createMock('Wirecard\PaymentSdk\Mapper\ResponseMapper');
        $responseMapper->method('map')->willReturn(null);

        $client->expects($this->at(0))
            ->method('request')->with(
                'GET',
                $this->anything(),
                $this->callback($checkRequestForShopData)
            )
            ->willReturn($httpResponse);

        $client->expects($this->at(1))
            ->method('request')->with(
                'POST',
                $this->anything(),
                $this->callback($checkRequestForShopData)
            )
            ->willReturn($httpResponse);

        $service = new TransactionService($this->config, null, $client, $requestMapper, $responseMapper);
        $service->pay($transaction);
    }
}
