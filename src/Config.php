<?php

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
     * Config constructor.
     * @param string $httpUser
     * @param string $httpPassword
     * @param string $merchantAccountId
     * @param string $secretKey
     */
    public function __construct($httpUser, $httpPassword, $merchantAccountId, $secretKey)
    {
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->merchantAccountId = $merchantAccountId;
        $this->secretKey = $secretKey;
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
}
