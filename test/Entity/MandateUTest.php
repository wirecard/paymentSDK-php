<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Mandate;

class MandateUTest extends \PHPUnit_Framework_TestCase
{
    const ID = '345';

    /**
     * @var Mandate
     */
    private $mandate;

    public function setUp()
    {
        $this->mandate = new Mandate(self::ID);
    }

    public function testMappedProperties()
    {
        $today = gmdate('Y-m-d');
        $expectedResult = [
            'mandate-id' => self::ID,
            'signed-date' => $today
        ];

        $result = $this->mandate->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }
}
