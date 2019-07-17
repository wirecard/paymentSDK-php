<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Periodic;

class PeriodicUTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructorWithNoParams()
    {
        $periodic = new Periodic();
        $this->assertNull($periodic->mappedProperties());
    }

    public function testConstructorWithFirstParam()
    {
        $periodic = new Periodic('ci');
        $this->assertEquals(['periodic-type' => 'ci'], $periodic->mappedProperties());
    }

    public function testConstructorWithSecondParam()
    {
        $periodic = new Periodic(null, 'first');
        $this->assertEquals(['sequence-type' => 'first'], $periodic->mappedProperties());
    }

    public function testConstructorWithBothParams()
    {
        $periodic = new Periodic('ci', 'first');
        $this->assertEquals(['periodic-type' => 'ci', 'sequence-type' => 'first'], $periodic->mappedProperties());
    }

    public function testSetPeriodicType()
    {
        $periodic = new Periodic();
        $periodic->setPeriodicType('ci');
        $this->assertEquals(['periodic-type' => 'ci'], $periodic->mappedProperties());
    }

    public function testSetSequenceType()
    {
        $periodic = new Periodic();
        $periodic->setSequenceType('first');
        $this->assertEquals(['sequence-type' => 'first'], $periodic->mappedProperties());
    }

    public function testAllSetters()
    {
        $periodic = new Periodic();
        $periodic->setPeriodicType('ci');
        $periodic->setSequenceType('first');
        $this->assertEquals(['periodic-type' => 'ci', 'sequence-type' => 'first'], $periodic->mappedProperties());
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testUnexpectedValueExceptionPeriodicType()
    {
        new Periodic('first');
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testUnexpectedValueExceptionSequenceType()
    {
        new Periodic(null, 'second');
    }

    public function testSeamlessMappedProperties()
    {
        $periodic = new Periodic();
        $periodic->setPeriodicType('ci');
        $periodic->setSequenceType('first');
        $this->assertEquals(
            ['periodic_type' => 'ci', 'sequence_type' => 'first'],
            $periodic->mappedSeamlessProperties()
        );
    }
}
