<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class PayPalTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class PayPalTransaction extends Transaction implements Reservable
{
    const NAME = 'paypal';

    /**
     * return string
     */
    public function getEndpoint()
    {
        if (null !== $this->parentTransactionId && $this->operation !== Operation::RESERVE) {
            return self::ENDPOINT_PAYMENTS;
        }

        return self::ENDPOINT_PAYMENT_METHODS;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $transactionType = $this->retrieveTransactionType();
        $data = array();

        if ($transactionType === self::TYPE_AUTHORIZATION_ONLY) {
            $data['periodic']['periodic-type'] = 'recurring';
            $data['periodic']['sequence-type'] = 'first';
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        if ($this->amount->getAmount() === 0.0) {
            return self::TYPE_AUTHORIZATION_ONLY;
        }

        return self::TYPE_AUTHORIZATION;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        if ($this->parentTransactionType) {
            return self::TYPE_CAPTURE_AUTHORIZATION;
        }

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
        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
                $transactionType = self::TYPE_VOID_AUTHORIZATION;
                break;
            case self::TYPE_DEBIT:
                $transactionType = self::TYPE_REFUND_DEBIT;
                break;
            case self::TYPE_CAPTURE_AUTHORIZATION:
                $transactionType = self::TYPE_REFUND_CAPTURE;
                break;
            default:
                throw new UnsupportedOperationException('The transaction can not be canceled.');
        }

        return $transactionType;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForCredit()
    {
        return self::TYPE_PENDING_CREDIT;
    }
}
