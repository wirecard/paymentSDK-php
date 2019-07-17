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
 * Class RatepayInvoiceTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class RatepayInvoiceTransaction extends RatepayTransaction implements Reservable
{
    const NAME = 'ratepayinvoice';
    const PAYMENT_NAME = 'ratepay-invoice';

    /**
     * @return string
     */
    public function getConfigKey()
    {
        return self::PAYMENT_NAME;
    }
}
