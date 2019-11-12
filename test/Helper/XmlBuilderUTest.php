<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Helper;

use Wirecard\PaymentSdk\Helper\XmlBuilder;

class XmlBuilderUTest extends \PHPUnit_Framework_TestCase
{
    private $xmlBuilder;

    public function setUp()
    {
        $this->xmlBuilder = new XmlBuilder('mainNodeName');
    }

    /**
     * This test is skipped for the 5.6 version as a fatal error is thrown
     * for an invalid argument supplied to a method
     * @requires PHP 7.0
     */
    public function testConstructorException()
    {
        if (version_compare(PHP_VERSION, '7', '<')) {
            $this->markTestSkipped('Skip test for older php versions');
        }

        $this->expectException(\InvalidArgumentException::class);
        new XmlBuilder(new \stdClass());
    }

    public function testAddSimpleXmlObject()
    {
        $expected = new \SimpleXMLElement('<mainNodeName><test></test></mainNodeName>');
        $this->xmlBuilder->addSimpleXmlObject(new \SimpleXMLElement('<test></test>'));
        $this->assertEquals($expected, $this->xmlBuilder->getXml());
    }

    public function testAddRawObjectException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->xmlBuilder->addRawObject(new \stdClass(), '');
    }

    public function testAddRawObject()
    {
        $expected = new \SimpleXMLElement('<mainNodeName><testRaw key="value">123</testRaw></mainNodeName>');
        $this->xmlBuilder->addRawObject('testRaw', '123', ['key' => 'value']);
        $this->assertEquals($expected, $this->xmlBuilder->getXml());
    }

    /**
     * This test is skipped for the 5.6 version as a fatal error is thrown
     * for an invalid argument supplied to a method
     * @requires PHP 7.0
     */
    public function testAddAttributesException()
    {
        if (version_compare(PHP_VERSION, '7', '<')) {
            $this->markTestSkipped('Skip test for older php versions');
        }

        $this->expectException(\TypeError::class);
        $this->xmlBuilder->addAttributes(new \stdClass());
    }

    public function testAddAttributes()
    {
        $expected = new \SimpleXMLElement('<mainNodeName key="value"></mainNodeName>');
        $this->xmlBuilder->addAttributes(['key' => 'value']);
        $this->assertEquals($expected, $this->xmlBuilder->getXml());
    }
}
