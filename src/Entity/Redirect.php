<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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
            $mappedProperties['success_redirect_url'] = $this->successUrl;
        }

        if (null !== $this->cancelUrl) {
            $mappedProperties['cancel_redirect_url'] = $this->cancelUrl;
        }

        if (null !== $this->failureUrl) {
            $mappedProperties['fail_redirect_url'] = $this->failureUrl;
        }

        return $mappedProperties;
    }
}
