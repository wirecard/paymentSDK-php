<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Formatter;

/**
 * Interface PropertyFormatter
 * @package Wirecard\PaymentSdk\Formatter
 *
 * Represents a Formatter
 * It can be used to format properties
 * @since 3.8.0
 */
interface PropertyFormatter
{
    /**
     * @param $property
     * @param array $params
     * @return mixed
     * @since 3.8.0
     */
    public function formatProperty($property, $params);
}
