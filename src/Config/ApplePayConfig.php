<?php
/**
 * Shop System Payment SDK - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Config;

use Wirecard\PaymentSdk\Transaction\ApplePayTransaction;

class ApplePayConfig extends PaymentMethodConfig
{

    private $supportedNetworks = array();

    /** @var  string $merchantIdentifier */
    private $merchantIdentifier;

    /** @var  string $sslCertificatePath */
    private $sslCertificatePath;

    /** @var  string $sslCertificateKey */
    private $sslCertificateKey;

    /** @var  string $domainName */
    private $domainName;

    /** @var  string $shopName */
    private $shopName;

    /** @var string $sslCertificatePassword */
    private $sslCertificatePassword;

    /**
     * ApplePayConfig constructor.
     * @param string $merchantAccountId
     * @param string $secret
     */
    public function __construct($merchantAccountId, $secret)
    {
        parent::__construct(ApplePayTransaction::NAME, $merchantAccountId, $secret);
    }

    public function addSupportedNetworks($networks)
    {
        if (is_string($networks) && strpos($networks, ',')) {
            $networks = explode(',', $networks);
        }

        if (is_string($networks)) {
            $this->supportedNetworks[] = $networks;
        }

        if (is_array($networks)) {
            $this->supportedNetworks = array_merge($this->supportedNetworks, $networks);
        }
    }

    /**
     * @return string
     */
    public function getSupportedNetworks()
    {
        $this->supportedNetworks = array_map('trim', $this->supportedNetworks);
        return json_encode($this->supportedNetworks);
    }

    /**
     * gets the apple merchant identifier
     *
     * @return string
     */
    public function getMerchantIdentifier()
    {
        return $this->merchantIdentifier;
    }

    /**
     * sets the apple merchant identifier
     *
     * @param string $merchantIdentifier
     * @return $this
     */
    public function setMerchantIdentifier($merchantIdentifier)
    {
        $this->merchantIdentifier = $merchantIdentifier;
        return $this;
    }

    /**
     * get the path to the CURLOPT_SSLCERT
     *
     * @return string
     */
    public function getSslCertificatePath()
    {
        return $this->sslCertificatePath;
    }

    /**
     * set the absolute path to the CURLOPT_SSLCERT
     *
     * @param string $sslCertificatePath
     * @return $this
     */
    public function setSslCertificatePath($sslCertificatePath)
    {
        $this->sslCertificatePath = $sslCertificatePath;
        return $this;
    }

    /**
     * get the path to the CURLOPT_SSLKEY
     *
     * @return string
     */
    public function getSslCertificateKey()
    {
        return $this->sslCertificateKey;
    }

    /**
     * set the absolute path to the CURLOPT_SSLKEY
     *
     * @param string $sslCertificateKey
     * @return $this
     */
    public function setSslCertificateKey($sslCertificateKey)
    {
        $this->sslCertificateKey = $sslCertificateKey;
        return $this;
    }

    /**
     * get the domain name
     *
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * set the domain name
     *
     * @param string $domainName
     * @return $this
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
        return $this;
    }

    /**
     * Get the shop display name
     *
     * @return string
     */
    public function getShopName()
    {
        return $this->shopName;
    }

    /**
     * set the display shop name
     *
     * @param string $shopName
     * @return $this
     */
    public function setShopName($shopName)
    {
        $this->shopName = $shopName;
        return $this;
    }

    public function setSslCertificatePassword($password)
    {
        $this->sslCertificatePassword = $password;
        return $this;
    }

    public function getSslCertificatePassword()
    {
        return $this->sslCertificatePassword;
    }
}
