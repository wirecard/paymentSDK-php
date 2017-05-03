<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
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
}
