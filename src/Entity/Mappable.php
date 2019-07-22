<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Formatter\DateFormatter;
use Wirecard\PaymentSdk\Formatter\PropertyFormatter;

/**
 * Interface MappableEntity
 * @package Wirecard\PaymentSdk\Entity
 *
 * Represents an entity which can be mapped
 * => it can be included in a request to Wirecard's Payment Processing Gateway.
 *
 * @since 3.8.0
 */
abstract class Mappable implements MappableEntity
{
    /** @const array PROPERTY_CONFIGURATION Used to configure the mapping of properties */
    const PROPERTY_CONFIGURATION = array();
    /** @const string MAP_KEY_SEAMLESS */
    const PROPERTY_MAP_SEAMLESS_KEY = 'mappedSeamless';
    /** @const string MAP_KEY */
    const PROPERTY_MAP_KEY = 'mapped';
    /** @var string PROPERTY_FORMATTER_KEY */
    const PROPERTY_FORMATTER_KEY = 'formatter';
    /** @var string PROPERTY_FORMATTER_PARAMS_KEY */
    const PROPERTY_FORMATTER_PARAMS_KEY = 'formatterParams';
    /** @var string PROPERTY_NAME_KEY */
    const PROPERTY_NAME_KEY = 'key';

    /** @var DateFormatter $dateFormatter */
    protected $dateFormatter;

    public function __construct()
    {
        $this->dateFormatter = new DateFormatter();
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        return $this->mapProperties(self::PROPERTY_MAP_KEY);
    }

    /**
     * @return array
     */
    public function mappedSeamlessProperties()
    {
        return $this->mapProperties(self::PROPERTY_MAP_SEAMLESS_KEY);
    }

    /**
     * @param $type
     * @return array
     */
    public function mapProperties($type)
    {
        $mappedArray = array();

        foreach (static::PROPERTY_CONFIGURATION as $property => $configuration) {
            if (!isset($this->{$property}) || !isset($configuration[$type])) {
                continue;
            }

            $configuration = $configuration[$type];

            $mappedArray[$configuration[self::PROPERTY_NAME_KEY]] = $this->getFormattedValue(
                $property,
                $this->getArrayValueByKey($configuration, self::PROPERTY_FORMATTER_KEY),
                $this->getArrayValueByKey($configuration, self::PROPERTY_FORMATTER_PARAMS_KEY)
            );
        }

        return $mappedArray;
    }

    /**
     * @param $array
     * @param $key
     * @return mixed
     */
    private function getArrayValueByKey($array, $key)
    {
        $return = null;
        if (isset($array[$key])) {
            $return = $array[$key];
        }

        return $return;
    }

    /**
     * @param $property
     * @param PropertyFormatter $formatter
     * @param array $formatterParams
     * @return mixed
     */
    private function getFormattedValue($property, $formatter, $formatterParams)
    {
        if (is_null($formatter) || !isset($this->{$formatter})) {
            return $this->{$property};
        }

        if (is_null($formatterParams)) {
            return $this->getFormattedValuePlain($property, $formatter);
        }

        return $this->getFormattedValueWithParameters($property, $formatter, $formatterParams);
    }

    /**
     * @param $property
     * @param $formatter
     * @return mixed
     */
    private function getFormattedValuePlain($property, $formatter)
    {
        return $this->{$formatter}->formatProperty(
            $this->{$property},
            array()
        );
    }

    /**
     * @param $property
     * @param $formatter
     * @param $formatterParams
     * @return mixed
     */
    private function getFormattedValueWithParameters($property, $formatter, $formatterParams)
    {
        return $this->{$formatter}->formatProperty(
            $this->{$property},
            $formatterParams
        );
    }
}
