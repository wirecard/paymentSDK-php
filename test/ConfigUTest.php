<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Config;

class ConfigUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $instance;

    public function setUp()
    {
        $this->instance = new Config('http://www.example.com', 'httpUser', 'httpPassword', 'merchantAccountId', 'secretKey');
    }

    public function testGetUrl()
    {
        $this->assertEquals('http://www.example.com', $this->instance->getUrl());
    }

    public function testGetHttpUser()
    {
        $this->assertEquals('httpUser', $this->instance->getHttpUser());
    }

    public function testGetHttpPassword()
    {
        $this->assertEquals('httpPassword', $this->instance->getHttpPassword());
    }

    public function testGetMerchantAccountId()
    {
        $this->assertEquals('merchantAccountId', $this->instance->getMerchantAccountId());
    }

    public function testGetSecretKey()
    {
        $this->assertEquals('secretKey', $this->instance->getSecretKey());
    }
}
