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
 * Class InfoAvailability
 * Contains possible item availability for credit card 3DS 2.X
 * Used to identify if an item is currently available
 * @package Wirecard\PaymentSdk\Constant
 * @since 3.8.0
 */
class RiskInfoAvailability extends Enum
{
    /** @var string Merchandise is currently available */
    const MERCHANDISE_AVAILABLE = '01';
    /** @var string Merchandise will be available in the future */
    const FUTURE_AVAILABILITY   = '02';
}
