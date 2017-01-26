<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Config;

class ConfigUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $instance;

    public function setUp() {
        $this->instance = new Config('httpUser', 'httpPassword', 'merchantAccountId', 'secretKey');
    }

    public function testGetHttpUser()
    {
        $this->assertEquals($this->instance->getHttpUser(), 'httpUser');
    }

    public function testGetHttpPassword()
    {
        $this->assertEquals($this->instance->getHttpPassword(), 'httpPassword');
    }

    public function testGetMerchantAccountId()
    {
        $this->assertEquals($this->instance->getMerchantAccountId(), 'merchantAccountId');
    }

    public function testGetSecretKey()
    {
        $this->assertEquals($this->instance->getSecretKey(), 'secretKey');
    }
}
