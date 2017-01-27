<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\RequestIdGenerator;

class RequestIdGeneratorUTest extends \PHPUnit_Framework_TestCase
{
    public function testRandomness()
    {
        $generator = new RequestIdGenerator();

        $requestId = $generator->generate();
        usleep(1);
        $laterRequestId = $generator->generate();

        $this->assertNotEquals($requestId, $laterRequestId);
    }
}
