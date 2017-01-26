<?php

namespace WirecardTest\PaymentSdk;

use GuzzleHttp\Client;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
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
        $this->config = new Config('httpUser', 'httpPassword', 'maid', 'secret');
        $this->instance = new TransactionService($this->config);
    }

    public function testFullConstructor()
    {
        $logger = new Logger('test');
        $httpClient = new Client(['base_uri' => 'http://www.example.com']);
        $instance = new TransactionService($this->config, $logger, $httpClient);

        $this->assertAttributeEquals($this->config, 'config', $instance);
        $this->assertAttributeEquals($logger, 'logger', $instance);
        $this->assertAttributeEquals($httpClient, 'httpClient', $instance);
    }

    public function testGetConfig()
    {
        $this->assertEquals($this->config, $this->instance->getConfig());
    }

    public function testGetLogger()
    {
        $logger = new Logger('wirecard_payment_sdk');
        $logger->pushHandler(new ErrorLogHandler());
        $this->assertEquals($logger, $this->instance->getLogger());
    }

    public function testGetLoggerReturnsLoggerInterface()
    {
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $this->instance->getLogger());
    }

    public function testGetHttpClient()
    {
        $this->assertEquals(new Client(), $this->instance->getHttpClient());
    }
}
