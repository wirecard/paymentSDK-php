<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\TransactionService;

class TransactionServiceUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionServicePublic
     */
    private $instance;

    /**
     * @var Config
     */
    private $config;

    public function setUp()
    {
        $this->config = $this->createMock('\Wirecard\PaymentSdk\Config');
        $this->instance = new TransactionServicePublic($this->config);
    }

    public function testFullConstructor()
    {
        $logger = $this->createMock('Monolog\Logger');
        $httpClient = $this->createMock('GuzzleHttp\Client');
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
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $this->instance->getLogger());
    }

    public function testGetHttpClient()
    {
        $this->assertInstanceOf('GuzzleHttp\Client', $this->instance->getHttpClient());
    }
}

class TransactionServicePublic extends TransactionService
{
    public function getLogger()
    {
        return parent::getLogger();
    }

    public function getConfig()
    {
        return parent::getConfig();
    }

    public function getHttpClient()
    {
        return parent::getHttpClient();
    }
}
