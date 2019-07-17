<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Exception;

use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

class MandatoryFieldMissingExceptionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MandatoryFieldMissingException
     */
    private $exception;

    public function setUp()
    {
        $this->exception = new MandatoryFieldMissingException('testMessage');
    }

    public function testIsRuntimeException()
    {
        $this->assertInstanceOf(\RuntimeException::class, $this->exception);
    }
}
