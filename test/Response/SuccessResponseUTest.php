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
                        <transaction-type>transaction</transaction-type>
                        <statuses>
                            <status code="1" description="a" severity="0" />
                        </statuses>
                    </payment>');
        $this->response = new SuccessResponse($this->simpleXml, false);
    }

    public function testGetTransactionType()
    {
        $this->assertEquals('transaction', $this->response->getTransactionType());
    }

    public function testIsValidSignature()
    {
        $this->assertEquals(false, $this->response->isValidSignature());
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
}
