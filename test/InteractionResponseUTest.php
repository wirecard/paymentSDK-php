<?php
namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\InteractionResponse;
use Wirecard\PaymentSdk\StatusCollection;

class InteractionResponseUTest extends \PHPUnit_Framework_TestCase
{
    private $redirectUrl = 'http://www.example.com/redirect';

    private $rawData = '{\'raw\': \'data\'}';

    private $transactionId = '42';
    private $statusCollection;

    /**
     * @var InteractionResponse
     */
    private $response;

    public function setUp()
    {
        $this->statusCollection = new StatusCollection();
        $this->response = new InteractionResponse(
            $this->rawData,
            $this->statusCollection,
            $this->transactionId,
            $this->redirectUrl
        );
    }

    public function testGetRawResponse()
    {
        $this->assertEquals($this->rawData, $this->response->getRawData());
    }

    public function testGetStatusCollection()
    {
        $this->assertEquals($this->statusCollection, $this->response->getStatusCollection());
    }

    public function testGetRedirectUrl()
    {
        $this->assertEquals($this->redirectUrl, $this->response->getRedirectUrl());
    }

    public function testGetTransactionId()
    {
        $this->assertEquals($this->transactionId, $this->response->getTransactionId());
    }
}
