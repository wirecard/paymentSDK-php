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
 * Interface MappableEntity
 * @package Wirecard\PaymentSdk\Entity
 *
 * Represents an entity which can be mapped
 * => it can be included in a request to Wirecard's Payment Processing Gateway.
 */
interface MappableEntity
{
    /**
     * @return array
     */
    public function mappedProperties();
}
