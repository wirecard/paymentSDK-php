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
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
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
