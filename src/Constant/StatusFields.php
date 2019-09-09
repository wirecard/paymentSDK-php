<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Constant;

/**
 * Contains mappable fields for the statuses
 *
 * Class StatusFields
 * @package Wirecard\PaymentSdk\Constant
 */
class StatusFields {
    const CODE = 'code';
    const SEVERITY = 'severity';
    const DESCRIPTION = 'description';

    const PATTERN = 'status_';
    const CODE_PATTERN = self::PATTERN . self::CODE . '_';
    const SEVERITY_PATTERN = self::PATTERN . self::SEVERITY . '_';
    const DESCRIPTION_PATTERN = self::PATTERN . self::DESCRIPTION . '_';
}