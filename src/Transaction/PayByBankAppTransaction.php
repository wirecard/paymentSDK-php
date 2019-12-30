<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

class PayByBankAppTransaction extends Transaction
{
    const NAME = 'zapp';

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        return array();
    }

    protected function retrieveTransactionTypeForPay()
    {
        return self::TYPE_DEBIT;
    }

    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        return Transaction::TYPE_REFUND_REQUEST;
    }
}
