<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Response;

use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Transaction\Operation;

class ResponseUTest extends \PHPUnit_Framework_TestCase
{
    public function testGetNormalCustomField()
    {
        $inputXml = simplexml_load_string('
            <raw>
                <request-id>123</request-id>
                <statuses>
                    <status code="1" description="a" severity="0"/>
                </statuses>
                <custom-fields>
                    <custom-field field-name="paysdk_testfield1" field-value="value1"/>
                </custom-fields>
            </raw>');
        $response = $this->getMockForAbstractClass(Response::class, [$inputXml]);

        $customFields = $response->getCustomFields();

        $customField = $customFields->getIterator()->offsetGet(0);
        $this->assertTrue($customField instanceof CustomField);
        $this->assertEquals('testfield1', $customField->getName());
        $this->assertEquals('value1', $customField->getValue());
    }

    public function testGetRawCustomField()
    {
        $inputXml = simplexml_load_string('
            <raw>
                <request-id>123</request-id>
                <statuses>
                    <status code="1" description="a" severity="0"/>
                </statuses>
                <custom-fields>
                    <custom-field field-name="utestprefix_testfield2" field-value="value2"/>
                </custom-fields>
            </raw>');
        $response = $this->getMockForAbstractClass(Response::class, [$inputXml]);

        $customFields = $response->getCustomFields();

        $customField = $customFields->getIterator()->offsetGet(0);
        $this->assertTrue($customField instanceof CustomField);
        $this->assertEquals('utestprefix_testfield2', $customField->getName());
        $this->assertEquals('value2', $customField->getValue());
    }

    public function testIgnoreEmptyCustomFields()
    {
        $inputXml = simplexml_load_string('
            <raw>
                <request-id>123</request-id>
                <statuses>
                    <status code="1" description="a" severity="0"/>
                </statuses>
                <custom-fields>
                    <custom-field field-name="utestprefix_testfield2"/>
                    <custom-field field-value="value2"/>
                </custom-fields>
            </raw>');
        $response = $this->getMockForAbstractClass(Response::class, [$inputXml]);

        $customFields = $response->getCustomFields();

        $this->assertEquals(0, $customFields->getIterator()->count());
    }

    public function testSetOperation()
    {
        $inputXml = simplexml_load_string('
            <raw>
                <request-id>123</request-id>
                <statuses>
                    <status code="1" description="a" severity="0"/>
                </statuses>
            </raw>');
        $response = $this->getMockForAbstractClass(Response::class, [$inputXml]);

        $response->setOperation(Operation::PAY);

        $this->assertEquals(Operation::PAY, $response->getOperation());
    }
}
