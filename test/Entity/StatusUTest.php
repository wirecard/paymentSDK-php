<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of SDK within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
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
