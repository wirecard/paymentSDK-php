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
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\UnsupportedOperationException;

class CustomFieldCollectionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomFieldCollection
     */
    private $customFieldCollection;

    public function setUp()
    {
        $this->customFieldCollection = new CustomFieldCollection();
    }

    public function testAdd()
    {
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);

        $this->assertAttributeEquals([$customField], 'customFields', $this->customFieldCollection);
    }

    public function testGetIterator()
    {
        $this->assertEquals(new \ArrayIterator([]), $this->customFieldCollection->getIterator());
    }

    public function testMappedProperties()
    {
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);

        $expected = [
            'custom-field' => [
                [
                    'field-name' => $customField->getPrefix() . 'test',
                    'field-value' => 'abc'
                ]
            ]
        ];
        $this->assertEquals($expected, $this->customFieldCollection->mappedProperties());
    }

    public function testGet()
    {
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $this->assertEquals('abc', $this->customFieldCollection->get('test'));
    }

    public function testGetForUnsetField()
    {
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $this->assertEquals(null, $this->customFieldCollection->get('test_not_set'));
    }

    public function testMappedSeamlessProperties()
    {
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);

        $expected = [
            'field_name_1' => $customField->getPrefix() . 'test',
            'field_value_1' => 'abc'
        ];
        $this->assertEquals($expected, $this->customFieldCollection->mappedSeamlessProperties());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMappedSeamlessPropertiesMax()
    {
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);
        $customField = new CustomField('test', 'abc');
        $this->customFieldCollection->add($customField);

        $data = $this->customFieldCollection->mappedSeamlessProperties();
    }
}
