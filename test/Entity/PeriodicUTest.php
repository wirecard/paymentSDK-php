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
