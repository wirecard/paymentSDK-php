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
    private $url;

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
    private $merchantAccountId;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $defaultCurrency = 'EUR';

    /**
     * Config constructor.
     * @param string $url
     * @param string $httpUser
     * @param string $httpPassword
     * @param string $merchantAccountId
     * @param string $secretKey
     */
    public function __construct($url, $httpUser, $httpPassword, $merchantAccountId, $secretKey)
    {
        $this->url = $url;
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->merchantAccountId = $merchantAccountId;
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
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
    public function getMerchantAccountId()
    {
        return $this->merchantAccountId;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @return string
     */
    public function getDefaultCurrency()
    {
        return $this->defaultCurrency;
    }

    /**
     * @param string $defaultCurrency
     * @return Config
     */
    public function setDefaultCurrency($defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
        return $this;
    }
}
