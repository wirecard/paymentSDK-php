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

namespace Wirecard\PaymentSdk;

class RequestMapper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RequestIdGenerator
     */
    private $requestIdGenerator;

    /**
     * RequestMapper constructor.
     * @param Config $config
     * @param RequestIdGenerator $requestIdGenerator
     */
    public function __construct(Config $config, RequestIdGenerator $requestIdGenerator)
    {
        $this->config = $config;
        $this->requestIdGenerator = $requestIdGenerator;
    }

    /**
     * @param Transaction $transaction
     * @return string The transaction in JSON format.
     */
    public function map(Transaction $transaction)
    {
        if ($transaction instanceof PayPalTransaction) {
            $onlyPaymentMethod = ['payment-method' => [['name' => 'paypal']]];
            $onlyNotificationUrl = ['notification' => [['url' => $transaction->getNotificationUrl()]]];
            $requestId = $this->requestIdGenerator->generate();

            $result = ['payment' => [
                'merchant-account-id' => ['value' => $this->config->getMerchantAccountId()],
                'request-id' => $requestId,
                'transaction-type' => 'debit',
                'requested-amount' => $this->getAmountOfTransaction($transaction),
                'payment-methods' => $onlyPaymentMethod,
                'cancel-redirect-url' => $transaction->getRedirect()->getCancelUrl(),
                'success-redirect-url' => $transaction->getRedirect()->getSuccessUrl(),
                'notifications' => $onlyNotificationUrl
            ]];
            return json_encode($result);
        }

        if ($transaction instanceof CreditCardTransaction) {
            $requestId = $this->requestIdGenerator->generate();

            $result = ['payment' => [
                'merchant-account-id' => ['value' => $this->config->getMerchantAccountId()],
                'request-id' => $requestId,
                'transaction-type' => 'referenced-authorization',
                'parent-transaction-id' => $transaction->getTransactionId(),
                'requested-amount' => $this->getAmountOfTransaction($transaction),
                'ip-address' => $_SERVER['REMOTE_ADDR']
            ]];
            return json_encode($result);
        }
    }

    /**
     * @param Transaction $transaction
     * @return array
     */
    private function getAmountOfTransaction(Transaction $transaction)
    {
        return [
            'currency' => $transaction->getAmount()->getCurrency(),
            'value' => $transaction->getAmount()->getAmount()
        ];
    }
}
