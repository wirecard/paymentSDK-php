<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\CustomField;

class CustomFieldUTest extends \PHPUnit_Framework_TestCase
{
    public function testMappedProperties()
    {
        $customField = new CustomField('special1', 'hihihi');
        $expected = [
            'field-name' => CustomField::DEFAULT_PREFIX. 'special1',
            'field-value' => 'hihihi'
        ];

        $this->assertEquals($expected, $customField->mappedProperties());
    }

    public function testRawMappedProperties()
    {
        $key   = 'uTestKey';
        $value = 'uTestValue';
        $rawPrefix = '';
        $customField = new CustomField($key, $value, $rawPrefix);
        $expected = [
            'field-name'  => $key,
            'field-value' => $value
        ];

        $this->assertEquals($expected, $customField->mappedProperties());
    }
}
