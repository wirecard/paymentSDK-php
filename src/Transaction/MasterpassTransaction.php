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

class MasterpassTransaction extends Transaction implements Reservable
{
    const NAME = 'masterpass';

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        if ($this->operation === Operation::PAY
            && $this->retrieveTransactionTypeForPay() !== Transaction::TYPE_CAPTURE_AUTHORIZATION
            && !$this->accountHolder instanceof AccountHolder
        ) {
            throw new MandatoryFieldMissingException('Account holder is a mandatory field.');
        }

        $result = array();
        if ($this->parentTransactionId) {
            $result['payment-methods'] = ['payment-method' => [['name' => 'creditcard']]];
        }
        return $result;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        if ($this->parentTransactionId) {
            if ($this->parentTransactionType === Transaction::TYPE_PURCHASE) {
                return self::TYPE_REFERENCED_PURCHASE;
            }
            return self::TYPE_CAPTURE_AUTHORIZATION;
        }
        return self::TYPE_DEBIT;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if ($this->parentTransactionType === self::TYPE_PURCHASE) {
            return self::TYPE_REFUND_PURCHASE;
        }

        if ($this->parentTransactionType === self::TYPE_AUTHORIZATION) {
            return self::TYPE_VOID_AUTHORIZATION;
        }

        if ($this->parentTransactionType === self::TYPE_CAPTURE_AUTHORIZATION) {
            return self::TYPE_VOID_CAPTURE;
        }

        if ($this->parentTransactionType === self::TYPE_REFERENCED_PURCHASE) {
            return self::TYPE_VOID_PURCHASE;
        }

        throw new UnsupportedOperationException('Canceling ' . $this->parentTransactionType . ' is not supported.');
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        if ($this->amount->getValue() === 0.0) {
            return self::TYPE_AUTHORIZATION_ONLY;
        }
        return self::TYPE_AUTHORIZATION;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        if ($this->parentTransactionId) {
            return self::ENDPOINT_PAYMENTS;
        }
        return self::ENDPOINT_PAYMENT_METHODS;
    }
}
