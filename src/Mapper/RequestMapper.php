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

namespace Wirecard\PaymentSdk\Mapper;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class RequestMapper
 * @package Wirecard\PaymentSdk\Mapper
 */
class RequestMapper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Closure
     */
    private $requestIdGenerator;

    /**
     * RequestMapper constructor.
     * @param Config $config
     * @param \Closure $requestIdGenerator
     */
    public function __construct(Config $config, \Closure $requestIdGenerator)
    {
        $this->config = $config;
        $this->requestIdGenerator = $requestIdGenerator;
    }

    /**
     * @param Transaction $transaction
     * @param string $operation
     * @param null|string $parentTransactionType
     * @return string The transaction in JSON format.
     */
    public function map(Transaction $transaction, $operation, $parentTransactionType)
    {
        $requestId = call_user_func($this->requestIdGenerator);
        $commonProperties = [
            'request-id' => $requestId
        ];

        $paymentMethodConfig = $this->config->get(
            $transaction->getConfigKey($operation, $parentTransactionType)
        );
        $paymentMethodConfigProperties = $paymentMethodConfig->mappedProperties();

        $allProperties = array_merge(
            $commonProperties,
            $transaction->mappedProperties($operation, $parentTransactionType),
            $paymentMethodConfigProperties
        );

        $allProperties['merchant-account-id']['value'] = $this->config->get($transaction->getConfigKey())
            ->getMerchantAccountId();

        $result = [Transaction::PARAM_PAYMENT => $allProperties];

        return json_encode($result);
    }
}
