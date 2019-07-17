<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

class UpopTransaction extends Transaction
{
    const NAME = 'upop';

    /**
     * return string
     */
    public function getEndpoint()
    {
        if ($this->operation == Operation::CANCEL) {
            return self::ENDPOINT_PAYMENTS;
        }
        return self::ENDPOINT_PAYMENT_METHODS;
    }

    /**
     * @return array
     */
    public function mappedSpecificProperties()
    {
        return [];
    }

    /**
     * @throws MandatoryFieldMissingException
     * @return string
     */
    public function retrieveTransactionTypeForPay()
    {
        if (!$this->accountHolder instanceof AccountHolder) {
            throw new MandatoryFieldMissingException('Account Holder is a mandatory field.');
        }
        return Transaction::TYPE_DEBIT;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }
        if ($this->parentTransactionType != Transaction::TYPE_DEBIT) {
            throw new UnsupportedOperationException('Only debit can be refunded.');
        }
        return Transaction::TYPE_REFUND_DEBIT;
    }
}
