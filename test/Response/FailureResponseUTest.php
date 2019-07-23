<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Response;

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\Response;

class FailureResponseUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FailureResponse
     */
    private $response;

    public function setUp()
    {
        $simpleXml = simplexml_load_string('<raw>
                        <request-id>123</request-id>
                        <statuses><status code="1" description="a" severity="0"></status></statuses>
                    </raw>');
        $this->response = new FailureResponse($simpleXml, false);
    }

    public function testFailureResponseIsResponse()
    {
        $this->assertInstanceOf(Response::class, $this->response);
    }
}
