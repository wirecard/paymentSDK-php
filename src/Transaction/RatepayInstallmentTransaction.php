<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

/**
 * Class RatepayInstallmentTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class RatepayInstallmentTransaction extends RatepayTransaction implements Reservable
{
    const NAME = 'ratepayinstall';
    const PAYMENT_NAME = 'ratepay-install';

    /**
     * @return string
     */
    public function getConfigKey()
    {
        return self::PAYMENT_NAME;
    }
}
