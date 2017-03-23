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
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class PayPalTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class PaysafecardTransaction extends Transaction implements Reservable
{
    const NAME = 'paysafecard';

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
     * return string
     */
    public function getEndpoint()
    {
        if ($this->operation === Operation::RESERVE ||
            ($this->operation === Operation::PAY && null === $this->parentTransactionId)) {
            return $this::ENDPOINT_PAYMENT_METHODS;
        } else {
            return $this::ENDPOINT_PAYMENTS;
        }
    }

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        if (null !== $this->redirect) {
            return [
                'cancel-redirect-url' => $this->redirect->getCancelUrl(),
                'success-redirect-url' => $this->redirect->getSuccessUrl()
            ];
        }

        return [];
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        return $this::TYPE_AUTHORIZATION;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        if ($this->parentTransactionType === $this::TYPE_AUTHORIZATION) {
            return $this::TYPE_CAPTURE_AUTHORIZATION;
        }

        return $this::TYPE_DEBIT;
    }

    /**
     * @throws UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if ($this->parentTransactionType !== $this::TYPE_AUTHORIZATION) {
            throw new UnsupportedOperationException();
        }

        return 'void-' . $this->parentTransactionType;
    }
}
