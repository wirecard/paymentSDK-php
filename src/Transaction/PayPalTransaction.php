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

use Wirecard\PaymentSdk\Entity\Redirect;
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
     * @var Redirect
     */
    private $redirect;

    /**
     * @param Redirect $redirect
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $data = [self::PARAM_TRANSACTION_TYPE => $this->retrieveTransactionType()];

        if ($this->operation !== Operation::CANCEL) {
            $data['cancel-redirect-url'] = $this->redirect->getCancelUrl();
            $data['success-redirect-url'] = $this->redirect->getSuccessUrl();
        }

        return $data;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    private function retrieveTransactionType()
    {
        switch ($this->operation) {
            case Operation::RESERVE:
                if ($this->amount->getAmount() === 0.0) {
                    $transactionType = 'authorization-only';
                } else {
                    $transactionType = $this::TYPE_AUTHORIZATION;
                }
                break;
            case Operation::CANCEL:
                $transactionType = $this->retrieveTransactionTypeForCancel();
                break;
            case Operation::PAY:
                $transactionType = $this->retrieveTransactionTypeForPay();
                break;
            default:
                throw new UnsupportedOperationException();
        }

        return $transactionType;
    }

    /**
     * @throws MandatoryFieldMissingException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        switch ($this->parentTransactionType) {
            case $this::TYPE_AUTHORIZATION:
                $transactionType = $this::TYPE_VOID_AUTHORIZATION;
                break;
            case $this::TYPE_DEBIT:
                $transactionType = 'refund-debit';
                break;
            default:
                throw new MandatoryFieldMissingException(
                    'Parent transaction type is missing for cancel operation'
                );
        }

        return $transactionType;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        if ($this->parentTransactionType) {
            return $this::TYPE_CAPTURE_AUTHORIZATION;
        } else {
            return $this::TYPE_DEBIT;
        }
    }

    /**
     * return string
     */
    public function getEndpoint()
    {
        if (null !== $this->parentTransactionId && $this->operation !== Operation::RESERVE) {
            return $this::ENDPOINT_PAYMENTS;
        } else {
            return $this::ENDPOINT_PAYMENT_METHODS;
        }
    }
}
