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
 * Class IsoTransactionType
 * Contains possible transaction types for credit card 3DS 2.X
 * Used to identify the type of transaction being authenticated
 * @package Wirecard\PaymentSdk\Constant
 * @since 3.8.0
 */
class IsoTransactionType extends Enum
{
    /** @var string */
    const GOODS_SERVICE_PURCHASE      = '01';
    /** @var string */
    const CHECK_ACCEPTANCE            = '03';
    /** @var string */
    const ACCOUNT_FUNDING             = '10';
    /** @var string */
    const QUASI_CASH_TRANSACTION      = '11';
    /** @var string  */
    const PREPAID_ACTIVATION_AND_LOAN = '28';
}
