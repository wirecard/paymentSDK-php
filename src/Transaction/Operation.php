<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

class Operation
{
    const RESERVE = 'reserve';
    const PAY = 'pay';
    const CANCEL = 'cancel';
    const REFUND = 'refund';
    const CREDIT = 'credit';
}
