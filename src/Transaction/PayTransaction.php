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

use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Entity\PaymentMethod\PayPal;

/**
 * Class PayTransaction
 * @package Wirecard\PaymentSdk\Transaction
 *
 * Use this transaction object,
 * if you want to execute a payment.
 */
class PayTransaction implements Transaction
{
    /**
     * @var Money
     */
    private $amount;

    /**
     * @var PayPal
     */
    private $paymentTypeSpecificData;

    /**
     * PayTransaction constructor.
     * @param Money $amount
     */
    public function __construct(Money $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return Money
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return PayPal
     */
    public function getPaymentTypeSpecificData()
    {
        return $this->paymentTypeSpecificData;
    }

    /**
     * @param PayPal $paymentTypeSpecificData
     */
    public function setPaymentTypeSpecificData($paymentTypeSpecificData)
    {
        $this->paymentTypeSpecificData = $paymentTypeSpecificData;
    }

    public function mappedProperties()
    {
        $onlyPaymentMethod = ['payment-method' => [['name' => 'paypal']]];
        $onlyNotificationUrl = [
            'notification' => [['url' => $this->paymentTypeSpecificData->getNotificationUrl()]]
        ];

        return [
            'requested-amount' => $this->amount->mappedProperties(),
            self::PARAM_TRANSACTION_TYPE => 'debit',
            'payment-methods' => $onlyPaymentMethod,
            'cancel-redirect-url' => $this->paymentTypeSpecificData->getRedirect()->getCancelUrl(),
            'success-redirect-url' => $this->paymentTypeSpecificData->getRedirect()->getSuccessUrl(),
            'notifications' => $onlyNotificationUrl
        ];
    }


}
