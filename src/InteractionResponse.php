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
class InteractionResponse extends Response
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * InteractionResponse constructor.
     * @param string $rawData - JSON string holding the raw response data
     * @param StatusCollection $statusCollection
     * @param string $transactionId - unique transaction id for followups
     * @param string $redirectUrl - Redirect url of the external service provider
     */
    public function __construct($rawData, $statusCollection, $transactionId, $redirectUrl)
    {
        parent::__construct($rawData, $statusCollection);
        $this->transactionId = $transactionId;
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
     * get the redirect url used for external service provider redirects
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
