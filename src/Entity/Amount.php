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

use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

/**
 * Class Amount
 * @package Wirecard\PaymentSdk\Entity
 *
 * An immutable entity representing an amount: value and currency.
 */
class Amount implements MappableEntity
{
    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $currency;

    /**
     * Amount constructor.
     * @param float|int $value
     * @param string $currency
     * @throws MandatoryFieldMissingException
     */
    public function __construct($value, $currency)
    {
        if (!$this->isNumeric($value)) {
            throw new MandatoryFieldMissingException(
                'Amount must be a numeric value (float or int).'
            );
        }

        $this->currency = $currency;
        $this->value = (float) $value;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        return [
            'currency' => $this->currency,
            'value' => $this->value
        ];
    }

    /**
     * Performs a strict check if the given value is float or int.
     *
     * PHPs integrated is_numeric function also accepts strings which is
     * undesirable for our use case.
     *
     * @param $value
     * @return bool
     */
    private function isNumeric($value)
    {
        return is_float($value) || is_int($value);
    }
}
