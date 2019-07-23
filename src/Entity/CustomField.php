<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

/**
 * Class CustomField
 * @package Wirecard\PaymentSdk\Entity
 *
 * An immutable entity representing a custom field: value and currency.
 */
class CustomField implements MappableEntity
{
    const DEFAULT_PREFIX = 'paysdk_';

    /**
     * @var string
     */
    private $prefix = self::DEFAULT_PREFIX;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * CustomField constructor.
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value, $prefix = self::DEFAULT_PREFIX)
    {
        $this->name = $name;
        $this->value = $value;
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getMappedName()
    {
        return (is_null($this->prefix) ? '' : $this->prefix) . $this->name;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        return [
            'field-name'  => $this->getMappedName(),
            'field-value' => $this->getValue()
        ];
    }
}
