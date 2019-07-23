<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Traversable;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class CustomFieldCollection
 * @package Wirecard\PaymentSdk\Entity
 */
class CustomFieldCollection implements \IteratorAggregate, MappableEntity
{
    /**
     * @var array
     */
    private $customFields = [];

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->customFields);
    }

    /**
     * @param CustomField $customField
     * @return $this
     */
    public function add(CustomField $customField)
    {
        $this->customFields[] = $customField;

        return $this;
    }

    /**
     * @param string $fieldName
     * @return CustomField|null
     */
    protected function getFieldByName($fieldName)
    {
        /** @var CustomField $customField */
        foreach ($this->getIterator() as $customField) {
            if ($customField->getName() === $fieldName) {
                return $customField;
            }
        }
        return null;
    }

    /**
     * @param string $fieldName
     * @return string|null
     */
    public function get($fieldName)
    {
        $field = $this->getFieldByName($fieldName);
        if (!is_null($field)) {
            return $field->getValue();
        }
        return null;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $data = ['custom-field' => []];

        /**
         * @var CustomField $customField
         */
        foreach ($this->getIterator() as $customField) {
            $data['custom-field'][] = $customField->mappedProperties();
        }

        return $data;
    }

    public function mappedSeamlessProperties()
    {
        $data = array();
        $count = 1;

        /**
         * @var CustomField $customField
         */
        foreach ($this->getIterator() as $customField) {
            if ($count > 10) {
                throw new UnsupportedOperationException('Maximum allowed number of additional fields is 10.');
            }
            $data["field_name_$count"] = $customField->getPrefix() . $customField->getName();
            $data["field_value_$count"] = $customField->getValue();
            $count++;
        }

        return $data;
    }

    /**
     * Get html table with the set data
     * @param array $options
     * @return string
     * @from 3.2.0
     */
    public function getAsHtml($options = [])
    {
        $defaults = [
            'table_id' => null,
            'table_class' => null,
            'translations' => [
                'title' => 'Custom Fields'
            ]
        ];

        $options = array_merge($defaults, $options);
        $translations = $options['translations'];

        $html = "<table id='{$options['table_id']}' class='{$options['table_class']}'><tbody>";
        foreach ($this->getAllSetData() as $key => $value) {
            $html .= "<tr><td>" . $this->translate($key, $translations) . "</td><td>" . $value . "</td></tr>";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Return all set data
     * @return array
     * @from 3.2.0
     */
    private function getAllSetData()
    {
        $data = [];
        foreach ($this->customFields as $customField) {
            $data[$customField->getName()] = $customField->getValue();
        }

        return $data;
    }

    /**
     * Translate the table keys
     * @param $key
     * @param $translations
     * @return mixed
     * @from 3.2.0
     */
    private function translate($key, $translations)
    {
        if ($translations != null && isset($translations[$key])) {
            return $translations[$key];
        }

        return $key;
    }
}
