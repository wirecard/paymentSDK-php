<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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
