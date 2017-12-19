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

use Wirecard\PaymentSdk\Response\SuccessResponse;

class SuccessResponseUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SuccessResponse
     */
    private $response;

    /**
     * @var \SimpleXMLElement $simpleXml
     */
    private $simpleXml;

    public function setUp()
    {
        $this->simpleXml = simplexml_load_string('<?xml version="1.0"?>
                    <payment>
                        <transaction-id>1</transaction-id>
                        <request-id>123</request-id>
                        <parent-transaction-id>ca-6ed-b69</parent-transaction-id>
                        <transaction-type>transaction</transaction-type>
                        <payment-methods>
                            <payment-method name="paypal"/>
                        </payment-methods>
                        <statuses>
                            <status code="1" description="a" severity="0" />
                        </statuses>
                        <card-token>
                            <token-id>4748178566351002</token-id>
                            <masked-account-number>541333******1006</masked-account-number>
                        </card-token>
                        <three-d>
                            <cardholder-authentication-status>Y</cardholder-authentication-status>
                        </three-d>
                    </payment>');
        $this->response = new SuccessResponse($this->simpleXml, false);
    }

    private function getSimpleXmlWithout($tag)
    {
        $doc = new \DOMDocument();
        $doc->loadXml($this->simpleXml->asXML());
        $document = $doc->documentElement;
        $element = $document->getElementsByTagName($tag)->item(0);
        $element->parentNode->removeChild($element);
        return new \SimpleXMLElement($doc->saveXML());
    }

    public function testGetTransactionType()
    {
        $this->assertEquals('transaction', $this->response->getTransactionType());
    }

    public function testIsValidSignature()
    {
        $this->assertEquals(true, $this->response->isValidSignature());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testMultipleDifferentProviderTransactionIdsThrowException()
    {
        $xml = $this->simpleXml;
        $statuses = $xml->{'statuses'};
        /**
         * @var $statuses \SimpleXMLElement
         */
        $status2 = $statuses->addChild('status');
        $status2->addAttribute('provider-transaction-id', '123');
        $status2->addAttribute('code', '200');
        $status2->addAttribute('description', 'Ok.');
        $status2->addAttribute('severity', 'Information');
        $status3 = $statuses->addChild('status');
        $status3->addAttribute('provider-transaction-id', '456');
        $status3->addAttribute('code', '200');
        $status3->addAttribute('description', 'Ok.');
        $status3->addAttribute('severity', 'Information');

        new SuccessResponse($xml, false);
    }

    public function testGetPaymentMethod()
    {
        $response = new SuccessResponse($this->simpleXml, false);
        $this->assertEquals('paypal', $response->getPaymentMethod());
    }

    public function testGetPaymentMethodWithoutPaymentMethodArray()
    {
        $xml = $this->getSimpleXmlWithout('payment-methods');
        $response = new SuccessResponse($xml, false);
        $this->assertEquals('', $response->getPaymentMethod());
    }

    public function testGetPaymentMethodWithNoNameAttribute()
    {
        $xml = $this->getSimpleXmlWithout('payment-method');
        /** @var \SimpleXMLElement $pms */
        $pms = $xml->{'payment-methods'};
        $pms->addChild('payment-method');
        $response = new SuccessResponse($xml, false);
        $this->assertEquals(null, $response->getPaymentMethod());
    }

    public function testGetParentTransactionId()
    {
        $response = new SuccessResponse($this->simpleXml);
        $this->assertEquals('ca-6ed-b69', $response->getParentTransactionId());
    }

    public function testGetMaskedAccountNumber()
    {
        $response = new SuccessResponse($this->simpleXml);
        $this->assertEquals('541333******1006', $response->getMaskedAccountNumber());
    }

    public function testGetMaskedAccountNumberIfNoneSet()
    {
        $xml = $this->getSimpleXmlWithout('card-token');
        $response = new SuccessResponse($xml);
        $this->assertEquals(null, $response->getMaskedAccountNumber());
    }

    public function testGetCardholderAuthenticationStatus()
    {
        $response = new SuccessResponse($this->simpleXml);
        $this->assertEquals('Y', $response->getCardholderAuthenticationStatus());
    }

    public function testGetCardholderAuthenticationStatusIfNoneSet()
    {
        $xml = $this->getSimpleXmlWithout('three-d');
        $response = new SuccessResponse($xml);
        $this->assertEquals(null, $response->getCardholderAuthenticationStatus());
    }

    public function testGetParentTransactionIdIfNoneSet()
    {
        $xml = $this->getSimpleXmlWithout('parent-transaction-id');
        $response = new SuccessResponse($xml);
        $this->assertEquals(null, $response->getParentTransactionId());
    }

    public function testGetProviderTransactionReferenceIfNoneSet()
    {
        $response = new SuccessResponse($this->simpleXml);
        $this->assertEquals(null, $response->getProviderTransactionReference());
    }

    public function testGetProviderTransactionReference()
    {
        $this->simpleXml->addChild('provider-transaction-reference-id', 'trans-ref_value');
        $response = new SuccessResponse($this->simpleXml);
        $this->assertEquals('trans-ref_value', $response->getProviderTransactionReference());
    }

    public function testGetCardTokenId()
    {
        $response = new SuccessResponse($this->simpleXml);
        $this->assertEquals('4748178566351002', $response->getCardTokenId());
    }

    public function testGetCardTokenIfNoneSet()
    {
        $xml = $this->getSimpleXmlWithout('card-token');
        $response = new SuccessResponse($xml);
        $this->assertEquals(null, $response->getCardTokenId());
    }

    public function testGetData()
    {
        $expected = [
            "transaction-id" => '1',
            "request-id" => '123',
            "parent-transaction-id" => "ca-6ed-b69",
            "transaction-type" => "transaction",
            "payment-methods.0.name" => "paypal",
            "statuses.0.code" => '1',
            "statuses.0.description" => "a",
            "statuses.0.severity" => 0,
            "card-token.0.token-id" => 4748178566351002,
            "card-token.0.masked-account-number" => "541333******1006",
            "three-d.0.cardholder-authentication-status" => "Y"
        ];

        $this->assertEquals($expected, $this->response->getData());
    }
}
