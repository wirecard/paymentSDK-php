<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Constant;

use MyCLabs\Enum\Enum;

/**
 * Class DeliveryTimeFrame
 * Contains possible delivery time frames for credit card 3DS 2.X
 * Used to identify delivery time frames
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.8.0
 */
class RiskInfoDeliveryTimeFrame extends Enum
{
    /** @var string Electronic delivery */
    const ELECTRONIC_DELIVERY      = '01';
    /** @var string Same day shipping */
    const SAME_DAY_SHIPPING        = '02';
    /** @var string Overnight shipping */
    const OVERNIGHT_SHIPPING       = '03';
    /** @var string Two day or more shipping */
    const TWO_DAY_OR_MORE_SHIPPING = '04';
}
