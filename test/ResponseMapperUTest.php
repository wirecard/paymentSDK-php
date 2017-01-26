<?php
namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\FailureResponse;
use Wirecard\PaymentSdk\InteractionResponse;
use Wirecard\PaymentSdk\ResponseMapper;


class ResponseMapperUTest extends \PHPUnit_Framework_TestCase
{
    const STATUS_CODE = 'code';
    const STATUS_DESCRIPTION = 'description';
    const STATUS_SEVERITY = 'severity';

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
            'payment' => [
                'transaction-state' => 'failed',
                'statuses' => [
                    [self::STATUS_CODE => '200', self::STATUS_DESCRIPTION => 'UnitTest', self::STATUS_SEVERITY => 'information'],
                    [self::STATUS_CODE => '500', self::STATUS_DESCRIPTION => 'UnitTest Error', self::STATUS_SEVERITY => 'error'],
                ]
            ]
        ]);
        /**
         * @var $mapped FailureResponse
         */
        $mapped = $this->mapper->map($response);
        $this->assertInstanceOf(FailureResponse::class, $mapped);

        $this->assertCount(2, $mapped->getStatusCollection());
    }

    public function testTransactionStateSuccessReturnsFilledInteractionResponseObject()
    {
        $response = json_encode([
            'payment' => [
                'transaction-id' => '12345',
                'transaction-state' => 'success',
                'statuses' => [
                    [self::STATUS_CODE => '200', self::STATUS_DESCRIPTION => 'UnitTest', self::STATUS_SEVERITY => 'information'],
                ],
                'payment-methods' => [
                    'payment-method' => [
                        ['name' => 'paypal', 'url' => 'http://www.example.com/redirect-url']
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
}
