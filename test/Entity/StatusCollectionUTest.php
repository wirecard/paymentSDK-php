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
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Entity\StatusCollection;

class StatusCollectionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StatusCollection
     */
    private $statusCollection;

    public function setUp()
    {
        $this->statusCollection = new StatusCollection();
    }

    public function testAdd()
    {
        $onlyStatus = new Status(23, 'sth useful', 'info');
        $this->statusCollection->add($onlyStatus);

        $this->assertAttributeEquals([$onlyStatus], 'statuses', $this->statusCollection);
    }


    /**
     * @return array
     */
    public function hasStatusCodeProvider()
    {
        return [
            [true, [23]],
            [true, [24,23]],
            [false, [25]]
        ];
    }

    /**
     * @dataProvider hasStatusCodeProvider
     * @param $expected
     * @param $search
     */
    public function testHasStatusCode($expected, $search)
    {
        $onlyStatus = new Status(23, 'sth useful', 'info');
        $this->statusCollection->add($onlyStatus);

        $onlyStatus2 = new Status(24, 'sth useful254', 'warning');
        $this->statusCollection->add($onlyStatus2);

        $this->assertEquals($expected, $this->statusCollection->hasStatusCodes($search));
    }
}
