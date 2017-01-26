<?php

namespace Wirecard\PaymentSdk;

/**
 * Class Redirect
 * @package Wirecard\PaymentSdk
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
     */
    public function __construct($successUrl, $cancelUrl)
    {
        $this->successUrl = $successUrl;
        $this->cancelUrl = $cancelUrl;
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
}
