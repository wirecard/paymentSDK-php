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
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
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

namespace WirecardTest\PaymentSdk\Mapper;

use Wirecard\PaymentSdk\Mapper\ResponseMapper;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\PendingResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;

/**
 * Class ResponseMapperUTest
 * @package WirecardTest\PaymentSdk\Mapper
 * @method getPaymentMethod
 */
class ResponseMapperUTest extends \PHPUnit_Framework_TestCase
{
    const STATUSES = 'statuses';
    const STATUS = 'status';
    const STATUS_CODE = 'code';
    const STATUS_DESCRIPTION = 'description';
    const STATUS_SEVERITY = 'severity';

    const PAYMENT = 'payment';
    const PAYMENT_METHODS = 'payment-methods';
    const PAYMENT_METHOD = 'payment-method';
    const PAYMENT_METHOD_NAME = 'name';
    const PAYMENT_METHOD_URL = 'url';

    const TRANSACTION_STATE = 'transaction-state';
    const TRANSACTION_ID = 'transaction-id';

    const ATTRIBUTES = '@attributes';

    /**
     * @var ResponseMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->mapper = new ResponseMapper();
    }

    public function testTransactionStateFailedReturnsFailureResponseObject()
    {
        $response = '<payment>
                        <transaction-state>failed</transaction-state>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status code="200" description="UnitTest" severity="warning" />
                            <status code="500" description="UnitTest Error" severity="error" />
                        </statuses>
                    </payment>';
        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(FailureResponse::class, $mapped);
        /**
         * @var FailureResponse $mapped
         */
        $this->assertCount(2, $mapped->getStatusCollection());
    }

    public function testTransactionStateSuccessReturnsFilledInteractionResponseObject()
    {
        $response = simplexml_load_string('<?xml version="1.0"?><payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status code="200" description="UnitTest" severity="warning"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal" url="http://www.example.com/redirect-url"/>
                        </payment-methods>
                    </payment>')->asXML();

        /**
         * @var $mapped InteractionResponse
         */
        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(InteractionResponse::class, $mapped);
        /**
         * @var InteractionResponse $mapped
         */
        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('http://www.example.com/redirect-url', $mapped->getRedirectUrl());
        $this->assertCount(1, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    public function testCardTokenReturnsPaymentMethodCreditCard()
    {
        $helper = function () {
            $this->simpleXml = new \SimpleXMLElement('<xml><card-token>123</card-token></xml>');
            return $this->getPaymentMethod();
        };
        $method = $helper->bindTo($this->mapper, $this->mapper);

        $expected = new \SimpleXMLElement('<payment-methods>
                                              <payment-method name="creditcard"></payment-method>
                                          </payment-methods>');

        $this->assertEquals($expected, $method());
    }

    public function testTransactionStateSuccessReturnsFilledSuccessResponseObject()
    {
        $response = $response = simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML();

        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        /**
         * @var SuccessResponse $mapped
         */
        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('W0RWI653B31MAU649', $mapped->getProviderTransactionId());
        $this->assertCount(1, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    public function testBase64encodedTransactionStateSuccessReturnsFilledSuccessResponseObject()
    {
        $response = base64_encode(simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML());

        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        /**
         * @var SuccessResponse $mapped
         */
        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('W0RWI653B31MAU649', $mapped->getProviderTransactionId());
        $this->assertCount(1, $mapped->getStatusCollection());
        $this->assertEquals(base64_decode($response), $mapped->getRawData());
    }

    public function testWithValidResponseThreeDTransactionReturnsFormInteractionResponse()
    {
        $payload = simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>check-enrollment</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <card-token></card-token>
                        <three-d>
                            <acs-url>https://www.example.com/acs</acs-url>
                            <pareq>request</pareq>
                        </three-d>
                    </payment>')->asXML();
        $transaction = new ThreeDCreditCardTransaction();

        /**
         * @var FormInteractionResponse $mapped
         */
        $mapped = $this->mapper->map($payload, Operation::RESERVE, $transaction);

        $this->assertInstanceOf(FormInteractionResponse::class, $mapped);
        $this->assertEquals($payload, $mapped->getRawData());
    }

    public function testWithValidResponseThreeDTransactionReturnsFormInteractionResponseWithMd()
    {
        $payload = simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>check-enrollment</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <card-token></card-token>
                        <three-d>
                            <acs-url>https://www.example.com/acs</acs-url>
                            <pareq>request</pareq>
                        </three-d>
                    </payment>')->asXML();
        $transaction = new ThreeDCreditCardTransaction();

        /**
         * @var FormInteractionResponse $mapped
         */
        $mapped = $this->mapper->map($payload, Operation::RESERVE, $transaction);

        $this->assertInstanceOf(FormInteractionResponse::class, $mapped);
        $this->assertEquals($payload, $mapped->getRawData());
        $this->assertEquals(
            '{"enrollment-check-transaction-id":"12345","operation-type":"authorization"}',
            base64_decode($mapped->getFormFields()->getIterator()['MD'])
        );
    }

    public function testWithValidResponseThreeDTransactionReturnsFormInteractionResponseWithTermUrl()
    {
        $payload = simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-type>check-enrollment</transaction-type>
                        <transaction-id>12345</transaction-id>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <card-token></card-token>
                        <three-d>
                            <acs-url>https://www.example.com/acs</acs-url>
                            <pareq>request</pareq>
                        </three-d>
                    </payment>')->asXML();
        $transaction = new ThreeDCreditCardTransaction();
        $transaction->setTermUrl('dummy URL');

        /**
         * @var FormInteractionResponse $mapped
         */
        $mapped = $this->mapper->map($payload, Operation::RESERVE, $transaction);

        $this->assertInstanceOf(FormInteractionResponse::class, $mapped);
        $this->assertEquals('dummy URL', $mapped->getFormFields()->getIterator()['TermUrl']);
    }

    public function testWithValidResponseCreditCardTransactionReturnsSuccessResponse()
    {
        $payload = simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <request-id>123</request-id>
                        <transaction-type>debit</transaction-type>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <card-token></card-token>
                    </payment>')->asXML();
        $transaction = new CreditCardTransaction();

        /**
         * @var FormInteractionResponse $mapped
         */
        $mapped = $this->mapper->map($payload, $transaction);

        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        $this->assertEquals($payload, $mapped->getRawData());
    }

    public function invalidResponseProvider()
    {
        return [
            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                    </payment>')->asXML(), null],
            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                        </payment-methods>
                    </payment>')->asXML(), null],
            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                            <payment-method name="eft"></payment-method>
                        </payment-methods>
                    </payment>')->asXML(), null],
            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML(), null],

            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses></statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML(), null],

            [simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <transaction-id>12345</transaction-id>
                        <statuses>
                            <status 
                            code="305.0000" 
                            description="paypal:Status before." 
                            provider-transaction-id="xxx" 
                            severity="information"/>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML(), null],
            [simplexml_load_string('<payment>
                           <transaction-state>success</transaction-state>
                           <transaction-id>12345</transaction-id>
                           <transaction-type>debit</transaction-type>
                           <request-id>123</request-id>
                           <statuses>
                               <status 
                               code="305.0000" 
                               description="paypal:Status before." 
                               provider-transaction-id="xxx" 
                               severity="information"/>
                               <status 
                               code="201.0000" 
                               description="paypal:The resource was successfully created." 
                               provider-transaction-id="W0RWI653B31MAU649" 
                               severity="information"/>
                           </statuses>
                  </payment>')->asXML(), $this->createMock(ThreeDCreditCardTransaction::class)],
            [simplexml_load_string('<payment>
                           <transaction-state>success</transaction-state>
                           <transaction-id>12345</transaction-id>
                           <transaction-type>debit</transaction-type>
                           <request-id>123</request-id>
                           <statuses>
                               <status 
                               code="305.0000" 
                               description="paypal:Status before." 
                               provider-transaction-id="xxx" 
                               severity="information"/>
                               <status 
                               code="201.0000" 
                               description="paypal:The resource was successfully created." 
                               provider-transaction-id="W0RWI653B31MAU649" 
                               severity="information"/>
                           </statuses>
                           <three-d></three-d>
                  </payment>')->asXML(), $this->createMock(ThreeDCreditCardTransaction::class)],
            [simplexml_load_string('<payment>
                           <transaction-state>success</transaction-state>
                           <transaction-id>12345</transaction-id>
                           <transaction-type>debit</transaction-type>
                           <request-id>123</request-id>
                           <statuses>
                               <status 
                               code="305.0000" 
                               description="paypal:Status before." 
                               provider-transaction-id="xxx" 
                               severity="information"/>
                               <status 
                               code="201.0000" 
                               description="paypal:The resource was successfully created." 
                               provider-transaction-id="W0RWI653B31MAU649" 
                               severity="information"/>
                           </statuses>
                           <three-d>
                               <acs-url>https://www.example.com/acs</acs-url>
                           </three-d>
                  </payment>')->asXML(), $this->createMock(ThreeDCreditCardTransaction::class)],
        ];
    }

    /**
     *
     */
    public function testMoreStatusesWithTheSameProviderTransactionIdReturnsSuccess()
    {
        $response = simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="305.0000" 
                            description="paypal:Status before." 
                            provider-transaction-id="xxx" 
                            severity="information"/>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="xxx" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML();

        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        /**
         * @var SuccessResponse $mapped
         */
        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('xxx', $mapped->getProviderTransactionId());
        $this->assertCount(2, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    public function testMoreStatusesOnlyOneHasProviderTransactionIdReturnsSuccess()
    {
        $response = simplexml_load_string('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="305.0000" 
                            description="paypal:Status before." 
                            severity="information"/>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="xxx" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>')->asXML();

        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        /**
         * @var SuccessResponse $mapped
         */
        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('xxx', $mapped->getProviderTransactionId());
        $this->assertCount(2, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }


    public function testTransactionStateInProgressReturnsPendingResponseObject()
    {
        $response = simplexml_load_string('<payment>
                        <transaction-state>in-progress</transaction-state>
                        <transaction-type>debit</transaction-type>
                        <request-id>1234</request-id>
                        <statuses><status code="1" description="a" severity="0"></status></statuses>
                    </payment>')->asXML();
        /**
         * @var PendingResponse $mapped
         */
        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(PendingResponse::class, $mapped);
        $this->assertEquals('1234', $mapped->getRequestId());
    }


    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     * @param $jsonResponse
     */
    public function testMalformedResponseThrowsException($jsonResponse)
    {
        $this->mapper->map($jsonResponse);
    }


    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     */
    public function testTransactionStateInProgressThrowsException()
    {
        $response = '<payment>
                        <transaction-state>in-progress</transaction-state>
                    </payment>';
        $this->mapper->map($response);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     */
    public function testMissingPaymentMethodsThrowsException()
    {
        $response = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="305.0000" 
                            description="paypal:Status before." 
                            severity="information"/>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="xxx" 
                            severity="information"/>
                        </statuses>
                    </payment>';
        $this->mapper->map($response);
    }


    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     */
    public function testEmptyPaymentMethodsThrowsException()
    {
        $response = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="305.0000" 
                            description="paypal:Status before." 
                            severity="information"/>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="xxx" 
                            severity="information"/>
                        </statuses>
                        <payment-methods></payment-methods>
                    </payment>';
        $this->mapper->map($response);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     */
    public function testMultiplePaymentMethodsThrowsException()
    {
        $response = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="305.0000" 
                            description="paypal:Status before." 
                            severity="information"/>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="xxx" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method></payment-method>
                            <payment-method></payment-method>
                        </payment-methods>
                    </payment>';
        $this->mapper->map($response);
    }


    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     */
    public function testMultipleDifferentProviderTransactionIDsThrowsException()
    {
        $response = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <transaction-type>debit</transaction-type>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="305.0000" 
                            description="paypal:Status before." 
                            provider-transaction-id="xxx" 
                            severity="information"/>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="yyy" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method></payment-method>
                        </payment-methods>
                    </payment>';
        $this->mapper->map($response);
    }


    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     */
    public function testMissingThreeDElementThrowsException()
    {
        $payload = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-type>check-enrollment</transaction-type>
                        <transaction-id>12345</transaction-id>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <card-token></card-token>
                    </payment>';
        $transaction = new ThreeDCreditCardTransaction();

        $this->mapper->map($payload, Operation::RESERVE, $transaction);
    }


    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     */
    public function testMissingAcsElementThrowsException()
    {
        $payload = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-type>check-enrollment</transaction-type>
                        <transaction-id>12345</transaction-id>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <card-token></card-token>
                        <three-d>
                            <pareq>request</pareq>
                        </three-d>
                    </payment>';
        $transaction = new ThreeDCreditCardTransaction();

        $this->mapper->map($payload, Operation::RESERVE, $transaction);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @dataProvider malformedResponseProvider
     */
    public function testMissingPareqElementThrowsException()
    {
        $payload = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-type>check-enrollment</transaction-type>
                        <transaction-id>12345</transaction-id>
                        <request-id>123</request-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <card-token></card-token>
                        <three-d>
                            <acs-url>https://www.example.com/acs</acs-url>
                        </three-d>
                    </payment>';
        $transaction = new ThreeDCreditCardTransaction();

        $this->mapper->map($payload, Operation::RESERVE, $transaction);
    }



    public function malformedResponseProvider()
    {
        $fullData = [
            self::TRANSACTION_STATE => 'success',
            self::PAYMENT_METHODS => [
                self::PAYMENT_METHOD => [
                    self::ATTRIBUTES => [
                        self::PAYMENT_METHOD_URL => 'http://www.example.com/redirect'
                    ],
                ],
            ],
            self::STATUSES => [
                self::STATUS => [
                    self::ATTRIBUTES => [
                        self::STATUS_CODE => 200,
                        self::STATUS_DESCRIPTION => 'PHPUnit description',
                        self::STATUS_SEVERITY => 'information'
                    ]
                ]
            ]
        ];
        $cases = [
            [self::TRANSACTION_STATE],
            [self::TRANSACTION_ID],
            [self::STATUSES, self::STATUS, self::ATTRIBUTES, self::STATUS_CODE],
            [self::STATUSES, self::STATUS, self::ATTRIBUTES, self::STATUS_DESCRIPTION],
            [self::STATUSES, self::STATUS, self::ATTRIBUTES, self::STATUS_SEVERITY],
            [self::PAYMENT_METHODS, self::PAYMENT_METHOD, self::ATTRIBUTES]
        ];

        $providerData = [
            ['']
        ];

        foreach ($cases as $case) {
            $value = $this->arrayToXml($this->removeResponseKey($fullData, $case));
            $providerData[] = [$value];
        }
        return $providerData;
    }

    private function arrayToXml($data, &$xmlData = null)
    {
        if ($xmlData === null) {
            $xmlData = new \SimpleXMLElement('<payment></payment>');
        }
        foreach ($data as $key => $value) {
            if ($key === self::ATTRIBUTES) {
                foreach ($value as $attribute => $content) {
                    $xmlData->addAttribute($attribute, $content);
                }
            } elseif (is_array($value)) {
                $subnode = $xmlData->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }
        return $xmlData->asXML();
    }

    private function removeResponseKey($response, array $key)
    {
        if (count($key) > 1) {
            $mainKey = array_shift($key);
            $subResponse = $response[$mainKey];
            $response[$mainKey] = $this->removeResponseKey($subResponse, $key);
        } else {
            unset($response[$key[0]]);
        }
        return $response;
    }
}
