<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
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
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Entity;

/**
 * Class Redirect
 * @package Wirecard\PaymentSdk\Entity
 *
 * An immutable entity representing redirect URL-s.
 */
class Redirect
{
    /**
     * @var string
     */
    private $successUrl;

    /**
     * @var string
     */
    private $cancelUrl;

    /**
     * @var string
     * @since 3.7.2
     */
    private $failureUrl;

    /**
     * Redirect constructor.
     * @param string $successUrl
     * @param string $cancelUrl
     * @param null $failureUrl
     */
    public function __construct($successUrl, $cancelUrl = null, $failureUrl = null)
    {
        $this->successUrl = $successUrl;
        $this->cancelUrl = $cancelUrl;
        $this->failureUrl = $failureUrl;
    }

    /**
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->successUrl;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @return string
     * @sice 3.7.2
     */
    public function getFailureUrl()
    {
        return $this->failureUrl;
    }

    /**
     * @return array
     * @since 3.7.2
     */
    public function mappedSeamlessProperties()
    {
        $mappedProperties = [];

        if (null !== $this->successUrl) {
            $mappedProperties['success-redirect-url'] = $this->successUrl;
        }

        if (null !== $this->cancelUrl) {
            $mappedProperties['cancel-redirect-url'] = $this->cancelUrl;
        }

        if (null !== $this->failureUrl) {
            $mappedProperties['fail-redirect-url'] = $this->failureUrl;
        }

        return $mappedProperties;
    }
}
