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
     * @return null
     */
    public function getFailureUrl()
    {
        return $this->failureUrl;
    }
}
