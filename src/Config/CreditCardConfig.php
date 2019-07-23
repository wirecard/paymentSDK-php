<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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
     * CreditCardConfig constructor.
     * @param string|null $merchantAccountId
     * @param string|null $secret
     * @param string $paymentMethodName
     */
    public function __construct(
        $merchantAccountId = null,
        $secret = null,
        $paymentMethodName = CreditCardTransaction::NAME
    ) {
        parent::__construct($paymentMethodName, null, null);

        if (!is_null($merchantAccountId) && !is_null($secret)) {
            $this->setSSLCredentials($merchantAccountId, $secret);
        }
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
     * @param Amount $nonThreeDMaxLimit
     * @return CreditCardConfig
     * @since 3.2.0
     */
    public function addNonThreeDMaxLimit(Amount $nonThreeDMaxLimit)
    {
        return $this->addSslMaxLimit($nonThreeDMaxLimit);
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
     * @param string $merchantAccountId
     * @param string $secret
     * @return CreditCardConfig
     */
    public function setSSLCredentials($merchantAccountId, $secret)
    {
        $this->merchantAccountId = $merchantAccountId;
        $this->secret = $secret;
        return $this;
    }

    /**
     * @param $merchantAccountId
     * @param $secret
     * @return CreditCardConfig
     * @since 3.2.0
     */
    public function setNonThreeDCredentials($merchantAccountId, $secret)
    {
        return $this->setSSLCredentials($merchantAccountId, $secret);
    }

    /**
     * @param string $currency
     * @return float|null
     */
    public function getSslMaxLimit($currency)
    {
        return array_key_exists($currency, $this->sslMaxLimits) ? $this->sslMaxLimits[$currency] : null;
    }

    /**
     * @param $currency
     * @return float|null
     * @since 3.2.0
     */
    public function getNonThreeDMaxLimit($currency)
    {
        return $this->getSslMaxLimit($currency);
    }

    /**
     * @param string $currency
     * @return float|null
     */
    public function getThreeDMinLimit($currency)
    {
        return array_key_exists($currency, $this->threeDMinLimits) ? $this->threeDMinLimits[$currency] : null;
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
