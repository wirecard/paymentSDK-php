<?php

namespace Wirecard\PaymentSdk;

class SuccessResponse extends Response
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $providerTransactionId;


    public function __construct($rawData, $statusCollection, $transactionId, $providerTransactionId)
    {
        parent::__construct($rawData, $statusCollection);
        $this->transactionId = $transactionId;
        $this->providerTransactionId = $providerTransactionId;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getProviderTransactionId()
    {
        return $this->providerTransactionId;
    }
}
