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

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;

class CreditCardConfig extends PaymentMethodConfig
{
    /**
     * @var string[]
     */
    private $sslMaxLimits = [];

    /**
     * @var string[]
     */
    private $threeDMinLimits = [];

    /**
     * @var string
     */
    private $threeDMerchantAccountId;

    /**
     * @var string
     */
    private $threeDSecret;

    /**
     * SepaConfig constructor.
     * @param string $merchantAccountId
     * @param string $secret
     */
    public function __construct($merchantAccountId, $secret)
    {
        parent::__construct(CreditCardTransaction::NAME, $merchantAccountId, $secret);
    }

    /**
     * @param Amount $sslMaxLimit
     * @return CreditCardConfig
     */
    public function addSslMaxLimit(Amount $sslMaxLimit)
    {
        $this->sslMaxLimits[$sslMaxLimit->getCurrency()] = $sslMaxLimit->getValue();
        return $this;
    }

    /**
     * @param Amount $threeDMinLimit
     * @return CreditCardConfig
     */
    public function addThreeDMinLimit(Amount $threeDMinLimit)
    {
        $this->threeDMinLimits[$threeDMinLimit->getCurrency()] = $threeDMinLimit->getValue();
        return $this;
    }

    /**
     * @param string $threeDMerchantAccountId
     * @param string $threeDSecret
     * @return CreditCardConfig
     */
    public function setThreeDCredentials($threeDMerchantAccountId, $threeDSecret)
    {
        $this->threeDMerchantAccountId = $threeDMerchantAccountId;
        $this->threeDSecret = $threeDSecret;
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getSslMaxLimits()
    {
        return $this->sslMaxLimits;
    }

    /**
     * @return \string[]
     */
    public function getThreeDMinLimits()
    {
        return $this->threeDMinLimits;
    }

    /**
     * @return string
     */
    public function getThreeDMerchantAccountId()
    {
        return $this->threeDMerchantAccountId;
    }

    /**
     * @return string
     */
    public function getThreeDSecret()
    {
        return $this->threeDSecret;
    }
}
