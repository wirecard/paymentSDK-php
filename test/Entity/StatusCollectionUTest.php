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
