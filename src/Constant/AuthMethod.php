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
 * Class AuthMethod
 * Contains possible authentication parameters for credit card 3DS 2.X
 * Used to identify risk
 * @package Wirecard\PaymentSdk\Constant
 * @since 3.8.0
 */
class AuthMethod extends Enum
{
    /** @var string Cardholder not logged in */
    const GUEST_CHECKOUT = '01';
    /** @var string Login to the cardholder account using a shop user account */
    const USER_CHECKOUT  = '02';
    /** @var string Login to the cardholder account using federated ID */
    const FEDERATED_ID   = '03';
    /** @var string Login to the cardholder account using issuer credentials */
    const ISSUER_CRED    = '04';
    /** @var string Login to the cardholder account using third-party authentication */
    const THIRD_PARTY    = '05';
    /** @var string Login to the cardholder account using FIDO authentication */
    const FIDO_AUTH      = '06';
}
