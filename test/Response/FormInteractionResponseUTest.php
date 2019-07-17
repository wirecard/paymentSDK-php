<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Response;

use Wirecard\PaymentSdk\Entity\FormFieldMap;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;

class FormInteractionResponseUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SimpleXMLElement
     */
    private $simpleXml;

    private $url = 'https://www.example.com/redirect';

    /**
     * @var FormFieldMap
     */
    private $formFields;

    /**
     * @var FormInteractionResponse
     */
    private $response;

    public function setUp()
    {
        $this->formFields = $this->createMock(FormFieldMap::class);
        $this->simpleXml = simplexml_load_string('<raw>
                        <transaction-id>1-2-3</transaction-id>
                        <request-id>123</request-id>
                        <transaction-type>failed-transaction</transaction-type>
                        <statuses><status code="1" description="a" severity="0"></status></statuses>
                    </raw>');

        $this->response = new FormInteractionResponse(
            $this->simpleXml,
            $this->url
        );

        $this->response->setFormFields($this->formFields);
    }

    public function testGetRawResponse()
    {
        $this->assertEquals($this->simpleXml->asXml(), $this->response->getRawData());
    }

    public function testGetRedirectUrl()
    {
        $this->assertEquals($this->url, $this->response->getUrl());
    }

    public function testGetFormFields()
    {
        $this->assertEquals($this->formFields, $this->response->getFormFields());
    }

    public function testGetMethod()
    {
        $this->assertEquals('POST', $this->response->getMethod());
    }
}
