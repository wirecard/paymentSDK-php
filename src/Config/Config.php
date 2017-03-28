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
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Config;

use Monolog\Logger;
use Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;

/**
 * Class Config
 *
 * This object is needed to provide the transactionService with the necessary information
 * to communicate with the elastic engine
 * @package Wirecard\PaymentSdk
 */
class Config
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $httpUser;

    /**
     * @var string
     */
    private $httpPassword;

    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * @var array
     */
    private $paymentMethodConfigs = [];

    /**
     * @var int
     */
    private $logLevel;

    /**
     * @var string
     */
    private $shopSystem;

    /**
     * @var string
     */
    private $shopSystemVersion;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var string
     */
    private $pluginVersion;

    /**
     * Config constructor.
     * @param string $baseUrl
     * @param string $httpUser
     * @param string $httpPassword
     * @param string $defaultCurrency
     */
    public function __construct(
        $baseUrl,
        $httpUser,
        $httpPassword,
        $defaultCurrency = 'EUR'
    ) {
        $this->baseUrl = $baseUrl;
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->defaultCurrency = $defaultCurrency;

        // During development the default debug level is set to DEBUG
        $this->logLevel = Logger::DEBUG;

        $this->shopSystem = 'paymentSDK';
        $this->shopSystemVersion = '';
    }

    /**
     * @param string $shopSystem
     * @param string $shopSystemVersion
     */
    public function setShopInfo($shopSystem, $shopSystemVersion)
    {
        $this->shopSystem = $shopSystem;
        $this->shopSystemVersion = $shopSystemVersion;
    }

    /**
     * @param string $pluginName
     * @param string $pluginVersion
     */
    public function setPluginInfo($pluginName, $pluginVersion)
    {
        $this->pluginName = $pluginName;
        $this->pluginVersion = $pluginVersion;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getHttpUser()
    {
        return $this->httpUser;
    }

    /**
     * @return string
     */
    public function getHttpPassword()
    {
        return $this->httpPassword;
    }

    /**
     * @return string
     */
    public function getDefaultCurrency()
    {
        return $this->defaultCurrency;
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param int $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @return array
     */
    public function getShopHeader()
    {
        $data = array(
            'shop-system-name' => $this->shopSystem,
            'shop-system-version' => $this->shopSystemVersion
        );

        if ($this->pluginName && $this->pluginVersion) {
            $data['plugin-name'] = $this->pluginName;
            $data['plugin-version'] = $this->pluginVersion;
        }

        return array('headers' => $data);
    }

    /**
     * @param PaymentMethodConfig $paymentMethodConfig
     * @return $this
     */
    public function add(PaymentMethodConfig $paymentMethodConfig)
    {
        $this->paymentMethodConfigs[$paymentMethodConfig->getPaymentMethodName()] = $paymentMethodConfig;

        return $this;
    }

    /**
     * @param string $paymentMethodName
     * @return PaymentMethodConfig
     * @throws \Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException
     */
    public function get($paymentMethodName)
    {
        if (array_key_exists($paymentMethodName, $this->paymentMethodConfigs)) {
            return $this->paymentMethodConfigs[$paymentMethodName];
        }

        $fallbacks = [
            SepaTransaction::DIRECT_DEBIT => SepaTransaction::NAME,
            SepaTransaction::CREDIT_TRANSFER => SepaTransaction::NAME
        ];

        if (array_key_exists($paymentMethodName, $fallbacks)) {
            $fallbackConfigKey = $fallbacks[$paymentMethodName];
            if (array_key_exists($fallbackConfigKey, $this->paymentMethodConfigs)) {
                return $this->paymentMethodConfigs[$fallbackConfigKey];
            }
        }

        throw new UnconfiguredPaymentMethodException();
    }
}
