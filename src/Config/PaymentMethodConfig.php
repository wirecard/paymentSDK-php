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

use Wirecard\PaymentSdk\Entity\MappableEntity;

class PaymentMethodConfig implements MappableEntity
{
    /**
     * @var string
     */
    private $paymentMethodName;

    /**
     * @var string
     */
    private $merchantAccountId;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var array
     */
    private $specificProperties = [];

    /**
     * PaymentMethodConfig constructor.
     * @param string $paymentMethodName
     * @param string $merchantAccountId
     * @param string $secret
     */
    public function __construct($paymentMethodName, $merchantAccountId, $secret)
    {
        $this->paymentMethodName = $paymentMethodName;
        $this->merchantAccountId = $merchantAccountId;
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getPaymentMethodName()
    {
        return $this->paymentMethodName;
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
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addSpecificProperty($key, $value)
    {
        $this->specificProperties[$key] = $value;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        return array_merge([
            'merchant-account-id' => [
                'value' => $this->merchantAccountId
            ]
        ], $this->specificProperties);
    }
}
