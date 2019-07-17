<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Status;

class StatusUTest extends \PHPUnit_Framework_TestCase
{
    const CODE = 55;
    const DESCRIPTION = 'some error';
    const ERROR = 'error';

    /**
     * @var Status
     */
    private $status;

    public function setUp()
    {
        $this->status = new Status(self::CODE, self::DESCRIPTION, self::ERROR);
    }

    public function testGetCode()
    {
        $this->assertEquals(self::CODE, $this->status->getCode());
    }

    public function testGetDescription()
    {
        $this->assertEquals(self::DESCRIPTION, $this->status->getDescription());
    }

    public function testGetSeverity()
    {
        $this->assertEquals(self::ERROR, $this->status->getSeverity());
    }
}
