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

    public function testConstructorException()
    {
        $this->expectException(\TypeError::class);
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
        $this->expectException(\TypeError::class);
        $this->xmlBuilder->addRawObject(new \stdClass(), '');
    }

    public function testAddRawObject()
    {
        $expected = new \SimpleXMLElement('<mainNodeName><testRaw key="value">123</testRaw></mainNodeName>');
        $this->xmlBuilder->addRawObject('testRaw', '123', ['key' => 'value']);
        $this->assertEquals($expected, $this->xmlBuilder->getXml());
    }

    public function testAddAttributesException()
    {
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
