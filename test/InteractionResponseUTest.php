<?php
namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\InteractionResponse;

class InteractionResponseUTest extends \PHPUnit_Framework_TestCase
{
    private $redirectUrl = 'http://www.example.com/redirect';

    private $rawData = '{\'raw\': \'data\'}';

    private $transactionId = '42';

    /**
     * @var InteractionResponse
     */
    private $response;

    public function setUp()
    {
        $this->response = new InteractionResponse($this->transactionId, $this->rawData, $this->redirectUrl);
    }

    public function testGetRedirectUrl()
    {
        $this->assertEquals($this->redirectUrl, $this->response->getRedirectUrl());
    }

    public function testGetRawResponse()
    {
        $this->assertEquals($this->rawData, $this->response->getRawData());
    }

    public function testGetTransactionId()
    {
        $this->assertEquals($this->transactionId, $this->response->getTransactionId());
    }
}
