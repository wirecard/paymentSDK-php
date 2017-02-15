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

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\CreditCardTransaction;
use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\FormInteractionResponse;
use Wirecard\PaymentSdk\InteractionResponse;
use Wirecard\PaymentSdk\ResponseMapper;
use Wirecard\PaymentSdk\SuccessResponse;
use Wirecard\PaymentSdk\ThreeDCreditCardTransaction;

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
                        <statuses>
                            <status code="200" description="UnitTest" severity="warning" />
                            <status code="500" description="UnitTest Error" severity="error" />
                        </statuses>
                    </payment>';
        /**
         * @var $mapped FailureResponse
         */
        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(FailureResponse::class, $mapped);

        $this->assertCount(2, $mapped->getStatusCollection());
    }

    public function testTransactionStateSuccessReturnsFilledInteractionResponseObject()
    {
        $response = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <statuses>
                            <status code="200" description="UnitTest" severity="warning" />
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal" url="http://www.example.com/redirect-url"></payment-method>
                        </payment-methods>
                    </payment>';

        /**
         * @var $mapped InteractionResponse
         */
        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(InteractionResponse::class, $mapped);

        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('http://www.example.com/redirect-url', $mapped->getRedirectUrl());
        $this->assertCount(1, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    public function testTransactionStateSuccessReturnsFilledSuccessResponseObject()
    {
        $response = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
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
                    </payment>';

        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(SuccessResponse::class, $mapped);

        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('W0RWI653B31MAU649', $mapped->getProviderTransactionId());
        $this->assertCount(1, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    public function testBase64encodedTransactionStateSuccessReturnsFilledSuccessResponseObject()
    {
        $response = base64_encode('<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
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
                    </payment>');

        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(SuccessResponse::class, $mapped);

        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('W0RWI653B31MAU649', $mapped->getProviderTransactionId());
        $this->assertCount(1, $mapped->getStatusCollection());
        $this->assertEquals(base64_decode($response), $mapped->getRawData());
    }

    public function testWithValidResponseThreeDTransactionReturnsFormInteractionResponse()
    {
        $payload = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
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
                    </payment>';
        $transaction = $this->createMock(ThreeDCreditCardTransaction::class);

        /**
         * @var $mapped FormInteractionResponse
         */
        $mapped = $this->mapper->map($payload, $transaction);

        $this->assertInstanceOf(FormInteractionResponse::class, $mapped);
        $this->assertEquals($payload, $mapped->getRawData());
    }

    public function testWithValidResponseThreeDTransactionReturnsFormInteractionResponseWithMd()
    {
        $payload = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
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
                    </payment>';
        $transaction = $this->createMock(ThreeDCreditCardTransaction::class);

        /**
         * @var $mapped FormInteractionResponse
         */
        $mapped = $this->mapper->map($payload, $transaction);

        $this->assertInstanceOf(FormInteractionResponse::class, $mapped);
        $this->assertEquals($payload, $mapped->getRawData());
        $this->assertEquals(
            '{ enrollment-check-transaction-id:12345, operation-type:authorization }',
            $mapped->getFormFields()->getIterator()['MD']
        );
    }

    public function testWithValidResponseCreditCardTransactionReturnsSuccessResponse()
    {
        $payload = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <card-token></card-token>
                    </payment>';
        $transaction = $this->createMock(CreditCardTransaction::class);

        /**
         * @var $mapped FormInteractionResponse
         */
        $mapped = $this->mapper->map($payload, $transaction);

        $this->assertInstanceOf(SuccessResponse::class, $mapped);
        $this->assertEquals($payload, $mapped->getRawData());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\MalformedResponseException
     * @dataProvider invalidResponseProvider
     * @param $xmlResponse
     * @param $transaction
     */
    public function testInvalidResponseThrowsException($xmlResponse, $transaction)
    {
        $this->mapper->map($xmlResponse, $transaction);
    }

    public function invalidResponseProvider()
    {
        return [
            ['<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                    </payment>', null],
            ['<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <statuses>
                            <status 
                            code="201.0000" 
                            description="paypal:The resource was successfully created." 
                            provider-transaction-id="W0RWI653B31MAU649" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                        </payment-methods>
                    </payment>', null],
            ['<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
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
                    </payment>', null],
            ['<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>', null],

            ['<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
                        <statuses></statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>', null],

            ['<payment>
                        <transaction-state>success</transaction-state>
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
                    </payment>', null],
            ['<payment>
                           <transaction-state>success</transaction-state>
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
                  </payment>', $this->createMock(ThreeDCreditCardTransaction::class)],
            ['<payment>
                           <transaction-state>success</transaction-state>
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
                           <three-d></three-d>
                  </payment>', $this->createMock(ThreeDCreditCardTransaction::class)],
            ['<payment>
                           <transaction-state>success</transaction-state>
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
                           <three-d>
                               <acs-url>https://www.example.com/acs</acs-url>
                           </three-d>
                  </payment>', $this->createMock(ThreeDCreditCardTransaction::class)],
        ];
    }

    public function testMoreStatusesWithTheSameProviderTransactionIdReturnsSuccess()
    {
        $response = '<payment>
                        <transaction-state>success</transaction-state>
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
                            provider-transaction-id="xxx" 
                            severity="information"/>
                        </statuses>
                        <payment-methods>
                            <payment-method name="paypal"></payment-method>
                        </payment-methods>
                    </payment>';

        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(SuccessResponse::class, $mapped);

        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('xxx', $mapped->getProviderTransactionId());
        $this->assertCount(2, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    public function testMoreStatusesOnlyOneHasProviderTransactionIdReturnsSuccess()
    {
        $response = '<payment>
                        <transaction-state>success</transaction-state>
                        <transaction-id>12345</transaction-id>
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
                    </payment>';

        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(SuccessResponse::class, $mapped);

        $this->assertEquals('12345', $mapped->getTransactionId());
        $this->assertEquals('xxx', $mapped->getProviderTransactionId());
        $this->assertCount(2, $mapped->getStatusCollection());
        $this->assertEquals($response, $mapped->getRawData());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\MalformedResponseException
     * @dataProvider malformedResponseProvider
     * @param $jsonResponse
     */
    public function testMalformedResponseThrowsException($jsonResponse)
    {
        $this->mapper->map($jsonResponse);
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
