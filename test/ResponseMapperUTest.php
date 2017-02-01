<?php
namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\InteractionResponse;
use Wirecard\PaymentSdk\ResponseMapper;
use Wirecard\PaymentSdk\SuccessResponse;

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
