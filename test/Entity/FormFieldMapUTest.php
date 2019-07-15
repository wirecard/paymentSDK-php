<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\FormFieldMap;

class FormFieldMapUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormFieldMap
     */
    private $map;
    public function setUp()
    {
        $this->map = new FormFieldMap();
    }

    public function testAdd()
    {
        $this->map->add('test', 'entry');

        foreach ($this->map as $key => $value) {
            $this->assertEquals('test', $key);
            $this->assertEquals('entry', $value);
        }
    }
}
