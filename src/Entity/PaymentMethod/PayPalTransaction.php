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

namespace Wirecard\PaymentSdk\Entity\PaymentMethod;

use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class PayPal
 * @package Wirecard\PaymentSdk\Entity\PaymentMethod
 *
 * An immutable entity containing Paypal payment data.
 * It does not contain logic.
 */
class PayPalTransaction implements Transaction
{
    /**
     * @var string
     */
    private $notificationUrl;

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * @var Money
     */
    private $amount;

    /**
     * PayPalTransaction constructor.
     * @param string $notificationUrl
     * @param Redirect $redirect
     */
    public function __construct($notificationUrl, Redirect $redirect)
    {
        $this->notificationUrl = $notificationUrl;
        $this->redirect = $redirect;
    }

    /**
     * @return string
     */
    public function getNotificationUrl()
    {
        return $this->notificationUrl;
    }

    /**
     * @return Redirect
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * @param Money $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @param null $operation
     * @return array
     */
    public function mappedProperties($operation = null)
    {
        $onlyPaymentMethod = ['payment-method' => [['name' => 'paypal']]];
        $onlyNotificationUrl = [
            'notification' => [['url' => $this->notificationUrl]]
        ];

        return [
            'requested-amount' => $this->amount->mappedProperties(),
            self::PARAM_TRANSACTION_TYPE => $this->retrieveTransactionType($operation),
            'payment-methods' => $onlyPaymentMethod,
            'cancel-redirect-url' => $this->getRedirect()->getCancelUrl(),
            'success-redirect-url' => $this->getRedirect()->getSuccessUrl(),
            'notifications' => $onlyNotificationUrl
        ];
    }

    private function retrieveTransactionType($operation)
    {
        $transactionTypes = [
            Operation::PAY => 'debit'
        ];

        if (!array_key_exists($operation, $transactionTypes)) {
            throw new \Exception('Unsupported operation.');
        }

        return $transactionTypes[$operation];
    }
}
