<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use MyCLabs\Enum\Enum;

class IdealBic extends Enum
{
    const ABNANL2A = 'ABN Amro Bank';
    const ASNBNL21 = 'ASN Bank';
    const BUNQNL2A = 'bunq';
    const INGBNL2A = 'ING';
    const KNABNL2H = 'Knab';
    const RABONL2U = 'Rabobank';
    const RGGINL21 = 'Regio Bank';
    const SNSBNL2A = 'SNS Bank';
    const TRIONL2U = 'Triodos Bank';
    const FVLBNL22 = 'Van Lanschot Bankiers';
    const MOYONL21 = 'Moneyou';
    const HANDNL2A = 'Handelsbanken';
}
