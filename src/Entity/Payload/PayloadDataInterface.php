<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity\Payload;

/**
 * Interface PayloadDataInterface
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
interface PayloadDataInterface
{
    /**
     * @return string|array
     * @since 4.0.0
     */
    public function getData();

    /**
     * @return mixed
     * @since 4.0.0
     */
    public function getType();
}
