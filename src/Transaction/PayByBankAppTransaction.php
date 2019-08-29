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

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        return self::TYPE_DEBIT;
    }

    /**
     * @return string
     * @throws MandatoryFieldMissingException
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        return Transaction::TYPE_REFUND_REQUEST;
    }

    /**
     * return string
     */
    public function getEndpoint()
    {
        if (in_array($this->operation, [Operation::CANCEL, Operation::REFUND])) {
            return self::ENDPOINT_PAYMENTS;
        }

        return self::ENDPOINT_PAYMENT_METHODS;
    }

}
