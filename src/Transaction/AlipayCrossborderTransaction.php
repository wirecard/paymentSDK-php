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
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

class AlipayCrossborderTransaction extends Transaction
{
    const NAME = "alipay-xborder";

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
     * @throws MandatoryFieldMissingException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        if (!$this->accountHolder instanceof AccountHolder && $this->operation === Operation::PAY) {
            throw new MandatoryFieldMissingException('No account holder set.');
        }

        return [];
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        return self::TYPE_DEBIT;
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
