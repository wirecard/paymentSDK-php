<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Status;
use Wirecard\PaymentSdk\StatusCollection;

class StatusCollectionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StatusCollection
     */
    private $statusCollection;

    public function setUp()
    {
        $this->statusCollection = new StatusCollection();
    }

    public function testAdd()
    {
        $onlyStatus = new Status(23, 'sth useful', 'info');
        $this->statusCollection->add($onlyStatus);

        $foundStatusCode = 0;

        foreach ($this->statusCollection as $st) {
            $foundStatusCode = $st->getCode();
        }

        $this->assertEquals(23, $foundStatusCode);
    }
}
