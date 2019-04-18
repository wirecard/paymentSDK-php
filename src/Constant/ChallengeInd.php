<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of SDK within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Constant;

use MyCLabs\Enum\Enum;

/**
 * Class ChallengeInd
 * Contains possible challenge parameters for credit card 3DS 2.X
 * Used to decide if a challenge is requested for a transaction
 * @package Wirecard\PaymentSdk\Constant
 * @since 3.7.0
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
