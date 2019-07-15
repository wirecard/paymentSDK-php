<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Response;

use DOMDocument;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;
use Wirecard\PaymentSdk\Response\SuccessResponse;

class SuccessResponseUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SuccessResponse
     */
    private $response;

    /**
     * @var SimpleXMLElement $simpleXml
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
                        <completion-time-stamp>1234</completion-time-stamp>
                        <consumer-id>1</consumer-id>
                        <ip-address>127.0.0.1</ip-address>
                        <order-number>123</order-number>
                        <merchant-account-id>maid123123123</merchant-account-id>
                        <transaction-state>success</transaction-state>
                        <currency>EUR</currency>
                        <requested-amount>17.86</requested-amount>
                        <descriptor>descriptor</descriptor>
                        <payment-methods>
                            <payment-method name="paypal"/>
                        </payment-methods>
                        <statuses>
                            <status code="200" description="Test description" severity="information" />
                        </statuses>
                        <account-holder>
                            <first-name>Hr</first-name>
                            <last-name>E G H Küppers en/of MW M.J. Küpp</last-name>
                            <email>email@email.com</email>
                            <phone>123123123</phone>
                            <address>
                                <street1>address 12</street1>
                                <city>City</city>
                                <country>AT</country>
                                <postal-code>4962</postal-code>
                            </address>
                        </account-holder>
                        <shipping>
                            <first-name>Max</first-name>
                            <last-name>Musterman</last-name>
                            <phone>123123123</phone>
                            <address>
                                <street1>address 12</street1>
                                <city>City</city>
                                <country>AT</country>
                                <postal-code>4962</postal-code>
                            </address>
                            <email>email@email.com</email>
                        </shipping>
                        <custom-fields>
                            <custom-field field-name="orderId" field-value="451"/>
                            <custom-field field-name="shopName" field-value="shop"/>
                        </custom-fields>
                        <card-token>
                            <token-id>4748178566351002</token-id>
                            <masked-account-number>541333******1006</masked-account-number>
                        </card-token>
                        <three-d>
                            <cardholder-authentication-status>Y</cardholder-authentication-status>
                        </three-d>
                    </payment>');
        $this->response = new SuccessResponse($this->simpleXml);
    }

    private function getSimpleXmlWithout($tag)
    {
        $doc = new DOMDocument();
        $doc->loadXml($this->simpleXml->asXML());
        $document = $doc->documentElement;
        $element = $document->getElementsByTagName($tag)->item(0);
        $element->parentNode->removeChild($element);
        return new SimpleXMLElement($doc->saveXML());
    }

    public function testGetTransactionType()
    {
        $this->assertEquals('transaction', $this->response->getTransactionType());
    }

    public function testIsValidSignature()
    {
        $this->assertEquals(true, $this->response->isValidSignature());
    }

    public function testMultipleDifferentProviderTransactionIds()
    {
        $xml = $this->simpleXml;
        $statuses = $xml->{'statuses'};
        /**
         * @var $statuses \SimpleXMLElement
         */
        $status2 = $statuses->addChild('status');
        $status2->addAttribute('provider-transaction-id', 'C016768154324581511879');
        $status2->addAttribute('provider-code', '0');
        $status2->addAttribute('provider-message', 'Batch transaction pending');
        $status2->addAttribute('code', '201.0000');
        $status2->addAttribute('description', '3d-acquirer:The resource was successfully created.');
        $status2->addAttribute('severity', 'information');
        $status3 = $statuses->addChild('status');
        $status3->addAttribute('provider-transaction-id', 'C275923154324581440567');
        $status3->addAttribute('provider-code', '0');
        $status3->addAttribute('provider-message', 'Transaction OK');
        $status3->addAttribute('code', '200.1083');
        $status3->addAttribute('description', '3d-acquirer:Cardholder Successfully authenticated.');
        $status3->addAttribute('severity', 'information');
        $response = new SuccessResponse($xml);
        $expectedResponse = ['C016768154324581511879','C275923154324581440567'];
        $this->assertEquals($expectedResponse, $response->getProviderTransactionIds());
    }

    public function testGetPaymentMethod()
    {
        $response = new SuccessResponse($this->simpleXml);
        $this->assertEquals('paypal', $response->getPaymentMethod());
    }

    public function testGetPaymentMethodWithoutPaymentMethodArray()
    {
        $xml = $this->getSimpleXmlWithout('payment-methods');
        $response = new SuccessResponse($xml);
        $this->assertEquals('', $response->getPaymentMethod());
    }

    public function testGetPaymentMethodWithNoNameAttribute()
    {
        $xml = $this->getSimpleXmlWithout('payment-method');
        /** @var \SimpleXMLElement $pms */
        $pms = $xml->{'payment-methods'};
        $pms->addChild('payment-method');
        $response = new SuccessResponse($xml);
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
            "statuses.0.code" => '200',
            "statuses.0.description" => 'Test description',
            "statuses.0.severity" => 'information',
            "card-token.0.token-id" => '4748178566351002',
            "card-token.0.masked-account-number" => "541333******1006",
            "three-d.0.cardholder-authentication-status" => "Y",
            "payment-methods.0.payment-method" => '',
            "statuses.0.status" => '',
            'completion-time-stamp' => '1234',
            'consumer-id' => '1',
            'ip-address' => '127.0.0.1',
            'order-number' => '123',
            'merchant-account-id' => 'maid123123123',
            'transaction-state' => 'success',
            'currency' => 'EUR',
            'requested-amount' => '17.86',
            'descriptor' => 'descriptor',
            'account-holder.0.first-name' => 'Hr',
            'account-holder.0.last-name' => 'E G H Küppers en/of MW M.J. Küpp',
            'account-holder.0.email' => 'email@email.com',
            'account-holder.0.phone' => '123123123',
            'account-holder.0.address.0.street1' => 'address 12',
            'account-holder.0.address.0.city' => 'City',
            'account-holder.0.address.0.country' => 'AT',
            'account-holder.0.address.0.postal-code' => '4962',
            'shipping.0.first-name' => 'Max',
            'shipping.0.last-name' => 'Musterman',
            'shipping.0.phone' => '123123123',
            'shipping.0.address.0.street1' => 'address 12',
            'shipping.0.address.0.city' => 'City',
            'shipping.0.address.0.country' => 'AT',
            'shipping.0.address.0.postal-code' => '4962',
            'shipping.0.email' => 'email@email.com',
            'custom-fields.0.field-name' => 'shopName',
            'custom-fields.0.field-value' => 'shop',
            'custom-fields.0.custom-field' => '',
        ];

        $this->assertEquals($expected, $this->response->getData());
    }

    public function testWithoutOrderItemsContent()
    {
        $xml = $this->simpleXml;
        $xml->addChild('order-items');

        $response = new SuccessResponse($xml);
        $this->assertNull($response->getBasket());
    }

    public function testWithoutOrderItemsTaxAmount()
    {
        $xml = $this->simpleXml;

        $orderItems = $xml->addChild('order-items');
        $orderItem = $orderItems->addChild('order-item');
        $orderItem->addChild('name', 'Beanie with Logo');
        $orderItem->addChild('tax-amount', 3.42)->addAttribute('currency', 'EUR');
        $orderItem->addChild('amount', 21.42)->addAttribute('currency', 'EUR');

        $response = new SuccessResponse($xml);

        $this->assertEquals(
            [
                'name' => 'Beanie with Logo',
                'quantity' => 0,
                'amount' => [
                    'currency' => 'EUR',
                    'value' => 21.42
                ],
                'description' => '',
                'article-number' => ''
            ],
            $response->getBasket()->mappedProperties()['order-item'][0]
        );
    }

    public function testGetPaymentDetails()
    {
        $response = new SuccessResponse($this->simpleXml);

        $this->assertNotNull($response->getPaymentDetails()->getAsHtml());
    }

    public function testGetTransactionDetails()
    {
        $response = new SuccessResponse($this->simpleXml);

        $this->assertNotNull($response->getTransactionDetails()->getAsHtml());
    }

    public function testAccountHolderHtml()
    {
        $response = new SuccessResponse($this->simpleXml);

        $this->assertNotNull($response->getAccountHolder()->getAsHtml());
    }

    public function testAccountHolderWithoutAddress()
    {
        $simpleXml = simplexml_load_string('<?xml version="1.0"?>
                    <payment>
                        <transaction-id>1</transaction-id>
                        <request-id>123</request-id>
                        <parent-transaction-id>ca-6ed-b69</parent-transaction-id>
                        <transaction-type>transaction</transaction-type>
                        <completion-time-stamp>1234</completion-time-stamp>
                        <consumer-id>1</consumer-id>
                        <ip-address>127.0.0.1</ip-address>
                        <order-number>123</order-number>
                        <merchant-account-id>maid123123123</merchant-account-id>
                        <transaction-state>success</transaction-state>
                        <currency>EUR</currency>
                        <requested-amount>17.86</requested-amount>
                        <descriptor>descriptor</descriptor>
                        <payment-methods>
                            <payment-method name="paypal"/>
                        </payment-methods>
                        <statuses>
                            <status code="1" description="a" severity="0" />
                        </statuses>
                        <account-holder>
                            <first-name>Hr</first-name>
                            <last-name>E G H Küppers en/of MW M.J. Küpp</last-name>
                            <email>email@email.com</email>
                            <phone>123123123</phone>
                        </account-holder>
                    </payment>');
        $response = new SuccessResponse($simpleXml);

        $this->assertNotNull($response->getAccountHolder()->getAsHtml());
    }

    public function testShippingHtml()
    {
        $response = new SuccessResponse($this->simpleXml);

        $this->assertNotNull($response->getShipping()->getAsHtml());
    }

    public function testCustomFieldsHtml()
    {
        $response = new SuccessResponse($this->simpleXml);

        $this->assertNotNull($response->getCustomFields()->getAsHtml());
    }

    public function testGetCardHtml()
    {
        $response = new SuccessResponse($this->simpleXml);

        $this->assertNotNull($response->getCard()->getAsHtml());
    }
}
