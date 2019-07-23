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
 * Contains order occurrence for credit card 3DS 2.X
 * Used to identify if an order is a first time or reorder
 * @package Wirecard\PaymentSdk\Constant
 * @since 3.8.0
 */
class RiskInfoReorder extends Enum
{
    /** @var string First time ordered */
    const FIRST_TIME_ORDERED = '01';
    /** @var string Reordered */
    const REORDERED          = '02';
}
