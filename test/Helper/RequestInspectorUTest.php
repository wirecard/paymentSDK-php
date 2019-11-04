<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Helper\RequestInspector;

/**
 * Class RequestInspectorUTest
 * @package WirecardTest\PaymentSdk
 */
class RequestInspectorUTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsFalseOnNull()
    {
        $this->assertFalse(
            RequestInspector::isValidRequest(null)
        );
    }

    public function testItReturnsFalseOnEmptyRequest()
    {
        $this->assertFalse(
            RequestInspector::isValidRequest(array())
        );
    }

    public function testItReturnsFalseOnMissingStatuses()
    {
        $request = array(
            'payment' => array()
        );

        $this->assertFalse(
            RequestInspector::isValidRequest($request)
        );
    }

    public function testItReturnsFalseOnEmptyStatuses()
    {
        $request = array(
            'payment' => array(
                'statuses' => array()
            )
        );

        $this->assertFalse(
            RequestInspector::isValidRequest($request)
        );
    }

    public function testItReturnsFalseOnStatusNoAccess()
    {
        $request = array(
            'payment' => array(
                'statuses' => array(
                    'status' => array( 'code' => '403.1166' )
                )
            )
        );

        $this->assertFalse(
            RequestInspector::isValidRequest($request)
        );
    }

    public function testItReturnsTrueOnOtherStatuses()
    {
        $request = array(
            'payment' => array(
                'statuses' => array(
                    'status' => array( 'code' => '200.0000' )
                )
            )
        );

        $this->assertTrue(
            RequestInspector::isValidRequest($request)
        );
    }

    public function testItCorrectlyChecksForStatuses()
    {
        $status = [ '403.1166' ];

        $this->assertTrue(
            RequestInspector::hasStatus($status, [ '403.1166' ])
        );

        $this->assertFalse(
            RequestInspector::hasStatus($status, [ '200.0000' ])
        );
    }
}
