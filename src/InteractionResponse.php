<?php
namespace Wirecard\PaymentSdk;

/**
 * Class InteractionResponse
 *
 * This object is returned,
 * if the payment process was initialized successfully,
 * and an interaction with the consumer browser is required in order to continue it.
 * @package Wirecard\PaymentSdk
 */
class InteractionResponse
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $rawData;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * InteractionResponse constructor.
     * @param string $transactionId - unique transaction id for followups
     * @param string $rawData - JSON string holding the raw response data
     * @param string $redirectUrl - Redirect url of the external service provider
     */
    public function __construct($transactionId, $rawData, $redirectUrl)
    {
        $this->transactionId = $transactionId;
        $this->rawData = $rawData;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * get the unique transaction id for followup operations
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * get the raw response data of the called interface
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->rawData;
    }


    /**
     * get the redirect url used for external service provider redirects
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
