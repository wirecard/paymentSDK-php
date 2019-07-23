<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Config;

use Monolog\Logger;
use Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\MaestroTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;

/**
 * Class Config
 *
 * This object is needed to provide the transactionService with the necessary information
 * to communicate with the Wirecard's Payment Processing Gateway
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
     * @var string
     */
    private $publicKey;

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
        $defaultCurrency = 'EUR',
        $baseUrlWppv2 = ''
    ) {
        $this->baseUrl = $baseUrl;
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->defaultCurrency = $defaultCurrency;
        $this->baseUrlWppv2 = $baseUrlWppv2;
        // During development the default debug level is set to DEBUG
        $this->logLevel = Logger::DEBUG;

        $this->shopSystem = 'paymentSDK-php';

        $version = $this->getVersionFromFile(__DIR__ . '/../../VERSION');
        $this->shopSystemVersion = $version;
    }

    /**
     * @return string
     *
     * @since 3.7.1
     */
    public function getShopSystemVersion()
    {
        return $this->shopSystemVersion;
    }

    /**
     * @param string $versionFile
     * @return string
     */
    private function getVersionFromFile($versionFile)
    {
        $version = '';
        if (file_exists($versionFile)) {
            $version = file_get_contents($versionFile, null, null, 0, 10);
        }
        return trim($version, " \t\n\r\0\x0B");
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
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
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
     * Get shop information for nvp request
     *
     * @return array
     *
     * @since 3.7.1
     */
    public function getNvpShopInformation()
    {
        $data = array(
            'shop_system_name'    => $this->shopSystem,
            'shop_system_version' => $this->shopSystemVersion,
        );

        if ($this->pluginName && $this->pluginVersion) {
            $data['plugin_name']    = $this->pluginName;
            $data['plugin_version'] = $this->pluginVersion;
        }

        return $data;
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
            RatepayInvoiceTransaction::PAYMENT_NAME => RatepayInvoiceTransaction::NAME,
            RatepayInstallmentTransaction::PAYMENT_NAME => RatepayInstallmentTransaction::NAME,
            CreditCardTransaction::NAME => MaestroTransaction::NAME,
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
