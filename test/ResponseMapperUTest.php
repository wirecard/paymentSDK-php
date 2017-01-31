<?php
namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\InteractionResponse;
use Wirecard\PaymentSdk\MalformedResponseException;
use Wirecard\PaymentSdk\ResponseMapper;
use Wirecard\PaymentSdk\SuccessResponse;

class ResponseMapperUTest extends \PHPUnit_Framework_TestCase
{
    const STATUSES = 'statuses';
    const STATUS_CODE = 'code';
    const STATUS_DESCRIPTION = 'description';
    const STATUS_SEVERITY = 'severity';
    const PROVIDER_TRANSACTION_ID = 'provider-transaction-id';

    const PAYMENT = 'payment';
    const PAYMENT_METHODS = 'payment-methods';
    const PAYMENT_METHOD = 'payment-method';
    const PAYMENT_METHOD_NAME = 'name';
    const PAYMENT_METHOD_URL = 'url';

    const TRANSACTION_STATE = 'transaction-state';
    const TRANSACTION_ID = 'transaction-id';

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
        $response = json_encode([
            self::PAYMENT => [
                self::TRANSACTION_STATE => 'failed',
                self::STATUSES => [
                    ['status' =>
                        [
                            self::STATUS_CODE => '200',
                            self::STATUS_DESCRIPTION => 'UnitTest',
                            self::STATUS_SEVERITY => 'warning'
                        ]],
                    ['status' =>
                        [
                            self::STATUS_CODE => '500',
                            self::STATUS_DESCRIPTION => 'UnitTest Error',
                            self::STATUS_SEVERITY => 'error'
                        ],
                    ]]
            ]
        ]);
        /**
         * @var $mapped FailureResponse
         */
        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(FailureResponse::class, $mapped);

        $this->assertCount(2, $mapped->getStatusCollection());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\MalformedResponseException
     */
    public function testTransactionStateSuccessNoPaymentMethodThrowsMalformedResponseException()
    {
        $response = json_encode([
            self::PAYMENT => [
                self::TRANSACTION_ID => '12345',
                self::TRANSACTION_STATE => 'success',
                self::STATUSES => [['status' =>
                    [
                        self::STATUS_CODE => '200',
                        self::STATUS_DESCRIPTION => 'UnitTest',
                        self::STATUS_SEVERITY => 'information'
                    ],
                ]]
            ]
        ]);

        $this->mapper->map($response);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\MalformedResponseException
     */
    public function testTransactionStateSuccessNoSinglePaymentMethodThrowsMalformedResponseException()
    {
        $response = json_encode([
            self::PAYMENT => [
                self::TRANSACTION_ID => '12345',
                self::TRANSACTION_STATE => 'success',
                self::STATUSES => [['status' =>
                    [
                        self::STATUS_CODE => '200',
                        self::STATUS_DESCRIPTION => 'UnitTest',
                        self::STATUS_SEVERITY => 'information'
                    ],
                ]],
                self::PAYMENT_METHODS => [
                ]
            ]
        ]);

        $this->mapper->map($response);
    }

    public function testTransactionStateSuccessReturnsFilledInteractionResponseObject()
    {
        $response = json_encode([
            self::PAYMENT => [
                self::TRANSACTION_ID => '12345',
                self::TRANSACTION_STATE => 'success',
                self::STATUSES => [['status' =>
                    [
                        self::STATUS_CODE => '200',
                        self::STATUS_DESCRIPTION => 'UnitTest',
                        self::STATUS_SEVERITY => 'information'
                    ],
                ]],
                self::PAYMENT_METHODS => [
                    self::PAYMENT_METHOD => [
                        [
                            self::PAYMENT_METHOD_NAME => 'paypal',
                            self::PAYMENT_METHOD_URL => 'http://www.example.com/redirect-url'
                        ]
                    ]
                ]
            ]
        ]);

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
            self::PAYMENT => [
                self::TRANSACTION_STATE => 'success',
                self::PAYMENT_METHODS => [
                    self::PAYMENT_METHOD => [
                        [self::PAYMENT_METHOD_URL => 'http://www.example.com/redirect'],
                    ],
                ],
                self::STATUSES => [['status' =>
                    [
                        self::STATUS_CODE => 200,
                        self::STATUS_DESCRIPTION => 'PHPUnit description',
                        self::STATUS_SEVERITY => 'information'
                    ]
                ]]
            ]
        ];
        $cases = [
            [self::PAYMENT],
            [self::PAYMENT, self::TRANSACTION_STATE],
            [self::PAYMENT, self::TRANSACTION_ID],
            [self::PAYMENT, self::STATUSES, 0, self::STATUS_CODE],
            [self::PAYMENT, self::STATUSES, 0, self::STATUS_DESCRIPTION],
            [self::PAYMENT, self::STATUSES, 0, self::STATUS_SEVERITY],
            [self::PAYMENT, self::PAYMENT_METHODS, self::PAYMENT_METHOD, 0, self::PAYMENT_METHOD_URL]
        ];

        $providerData = [
            ['']
        ];

        foreach ($cases as $case) {
            $providerData[] = [json_encode($this->removeResponseKey($fullData, $case))];
        }
        return $providerData;
    }

    public function testTransactionStateSuccessReturnsSuccessResponseObject()
    {
        $response = json_encode([
            self::PAYMENT => [
                self::TRANSACTION_ID => '12345',
                self::TRANSACTION_STATE => 'success',
                self::STATUSES => [['status' =>
                    [
                        self::STATUS_CODE => '201',
                        self::STATUS_DESCRIPTION => 'UnitTest: The resource was successfully created.',
                        self::PROVIDER_TRANSACTION_ID => '55',
                        self::STATUS_SEVERITY => 'information'
                    ],
                ]],
                self::PAYMENT_METHODS => [
                    self::PAYMENT_METHOD => [
                        [
                            self::PAYMENT_METHOD_NAME => 'paypal'
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->mapper->map($response);

        $this->assertInstanceOf(SuccessResponse::class, $result);

        $this->assertEquals('12345', $result->getTransactionId());
        $this->assertCount(1, $result->getStatusCollection());
        $this->assertEquals($response, $result->getRawData());
        $this->assertEquals('55', $result->getProviderTransactionId());
    }

    public function testTransactionStateSuccessWithMoreStatusesReturnsSuccessResponseObject()
    {
        $response = json_encode([
            self::PAYMENT => [
                self::TRANSACTION_ID => '12345',
                self::TRANSACTION_STATE => 'success',
                self::STATUSES => [
                    ['status' =>
                    [
                        self::STATUS_CODE => '500',
                        self::STATUS_DESCRIPTION => 'UnitTest: Earlier error.',
                        self::STATUS_SEVERITY => 'error'
                    ],
                ],
                    ['status' =>
                        [
                            self::STATUS_CODE => '201',
                            self::STATUS_DESCRIPTION => 'UnitTest: The resource was successfully created.',
                            self::PROVIDER_TRANSACTION_ID => '55',
                            self::STATUS_SEVERITY => 'information'
                        ]
    ]
                ],
                self::PAYMENT_METHODS => [
                    self::PAYMENT_METHOD => [
                        [
                            self::PAYMENT_METHOD_NAME => 'paypal'
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->mapper->map($response);

        $this->assertInstanceOf(SuccessResponse::class, $result);

        $this->assertEquals('12345', $result->getTransactionId());
        $this->assertCount(2, $result->getStatusCollection());
        $this->assertEquals($response, $result->getRawData());
        $this->assertEquals('55', $result->getProviderTransactionId());
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
