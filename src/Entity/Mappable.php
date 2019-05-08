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
     * @const string
     * Default date format
     */
    const DATE_FORMAT = 'Ymd';

    /**
     * @const array
     * Used to configure the mapping of properties
     */
    const PROPERTY_CONFIGURATION = array();

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
                $configuration['type'],
                isset($configuration['formatter']) ? $configuration['formatter'] : false
            );
        }
        return $mappedArray;
    }

    /**
     * @param $property
     * @param $type
     * @param bool $formatter
     * @return mixed
     */
    public function getFormattedValue($property, $type, $formatter = false)
    {
        $prefixedFormatter = 'getFormatted' . $type;

        if ($formatter == false || !method_exists($this, $prefixedFormatter)) {
            return $this->{$property};
        }

        return $this->$prefixedFormatter($property);
    }

    /**
     * @param $property
     * @return string
     */
    public function getFormattedDate($property)
    {
        /** @var \DateTime $date */
        $date = $this->{$property};

        return $date->format(self::DATE_FORMAT);
    }
}
