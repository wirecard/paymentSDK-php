<?php
namespace WirecardTest\PaymentSdk;


use Wirecard\PaymentSdk\InteractionResponse;


class InteractionResponseUTest extends \PHPUnit_Framework_TestCase
{
    private $redirectUrlToTest = 'http://www.example.com/redirect';

    private $rawDataToTest = '{\'raw\': \'data\'}';

    private $transactionIdToTest = '42';

    /**
     * @var InteractionResponse
     */
    private $response;

    public function setUp()
    {
        $this->response = new InteractionResponse($this->transactionIdToTest, $this->rawDataToTest, $this->redirectUrlToTest);
    }

    public function testGetRedirectUrl()
    {
        $this->assertEquals($this->redirectUrlToTest, $this->response->getRedirectUrl());
    }

    public function testGetRawResponse()
    {
        $this->assertEquals($this->rawDataToTest, $this->response->getRawData());
    }

    public function testGetTransactionId()
    {
        $this->assertEquals($this->transactionIdToTest, $this->response->getTransactionId());
    }
}
