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
        $this->instance = new Config('httpUser', 'httpPassword', 'merchantAccountId', 'secretKey');
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
