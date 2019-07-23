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
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class PtwentyfourTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class PtwentyfourTransaction extends Transaction implements Reservable
{
    const NAME = 'p24';

    /**
     * Do not use no more than 20 characters and do not use special chars
     * as it can be misinterpreted by a bank system.
     */
    const DESCRIPTOR_LENGTH = 20;

    /**
     * Allowed characters:
     * '0-9','a-z','A-Z'
     */
    const DESCRIPTOR_ALLOWED_CHAR_REGEX = '/[^a-zA-Z0-9]/u';

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
    protected function mappedSpecificProperties()
    {
        if (null === $this->accountHolder && Operation::PAY === $this->operation) {
            throw new MandatoryFieldMissingException('Account Holder is a mandatory field.');
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
        return Transaction::TYPE_REFUND_REQUEST;
    }
}
