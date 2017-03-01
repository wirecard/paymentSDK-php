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
class PayPalTransaction extends Transaction
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
     * @param string|null $operation
     * @return array
     */
    public function mappedProperties($operation = null)
    {

        $specificProperties = [
            self::PARAM_TRANSACTION_TYPE => $this->retrieveTransactionType($operation),
            'cancel-redirect-url' => $this->redirect->getCancelUrl(),
            'success-redirect-url' => $this->redirect->getSuccessUrl()
        ];

        return array_merge(parent::mappedProperties($operation), $specificProperties);
    }

    /**
     * @param string|null $operation
     * @return string
     */
    private function retrieveTransactionType($operation)
    {
        $transactionTypes = [
            Operation::PAY => 'debit'
        ];

        if (!array_key_exists($operation, $transactionTypes)) {
            throw new UnsupportedOperationException();
        }

        return $transactionTypes[$operation];
    }
}
