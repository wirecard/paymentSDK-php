<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
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
