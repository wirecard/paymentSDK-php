<?php
/**
 * Created by IntelliJ IDEA.
 * User: timon.roenisch
 * Date: 16.03.2017
 * Time: 09:49
 */

namespace Response;


use Wirecard\PaymentSdk\Response\SuccessResponse;


class SuccessResponseUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SuccessResponse
     */
    private $response;


    public function setUp()
    {
        $rawData = '<raw>
                        <transaction-type>failed-transaction</transaction-type>
                        <transaction-id>1</transaction-id>
                        <request-id>123</request-id>
                        <statuses><status code="1" description="a" severity="0"></status></statuses>
                    </raw>';

        $this->response = new SuccessResponse($rawData);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function testMalformedXmlException()
    {
        $invalidData = '<xml>';

        new SuccessResponse($invalidData);
    }


    public function testGetTransactionType()
    {
        $this->assertEquals('failed-transaction', $this->response->getTransactionType());
    }
}
