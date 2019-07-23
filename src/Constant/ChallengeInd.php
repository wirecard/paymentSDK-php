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
 * Class ChallengeInd
 * Contains possible challenge parameters for credit card 3DS 2.X
 * Used to decide if a challenge is requested for a transaction
 * @package Wirecard\PaymentSdk\Constant
 * @since 3.8.0
 */
class ChallengeInd extends Enum
{
    /** @var string No preference set */
    const NO_PREFERENCE     = '01';
    /** @var string No challenge requested */
    const NO_CHALLENGE      = '02';
    /** @var string 3DS challenge requested */
    const CHALLENGE_THREED  = '03';
    /** @var string Mandate challenge requested */
    const CHALLENGE_MANDATE = '04';
}
