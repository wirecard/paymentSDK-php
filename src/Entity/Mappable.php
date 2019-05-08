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

namespace Wirecard\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Formatter\DateFormatter;
use Wirecard\PaymentSdk\Formatter\PropertyFormatter;

/**
 * Interface MappableEntity
 * @package Wirecard\PaymentSdk\Entity
 *
 * Represents an entity which can be mapped
 * => it can be included in a request to Wirecard's Payment Processing Gateway.
 */
abstract class Mappable implements MappableEntity
{
    /**
     * @const array
     * Used to configure the mapping of properties
     */
    const PROPERTY_CONFIGURATION = array();

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
        return $this->mapProperties('mapped');
    }

    /**
     * @return array
     */
    public function mappedSeamlessProperties()
    {
        return $this->mapProperties('mappedSeamless');
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

            $mappedArray[$configuration['key']] = $this->getFormattedValue(
                $property,
                isset($configuration['formatter']) ? $configuration['formatter'] : null,
                isset($configuration['formatterParams']) ? $configuration['formatterParams'] : null
            );
        }

        return $mappedArray;
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
