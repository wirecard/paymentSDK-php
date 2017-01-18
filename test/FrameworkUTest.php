<?php
namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Framework;

class FrameworkUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Framework
     */
    protected $framework;

    public function setUp()
    {
        $this->framework = new Framework();
    }

    public function testFramework()
    {
        $this->assertEquals('Hello world', $this->framework->hello('world'));
    }
}
