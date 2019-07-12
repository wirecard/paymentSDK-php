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
