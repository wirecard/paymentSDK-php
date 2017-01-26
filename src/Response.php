<?php

namespace Wirecard\PaymentSdk;

/**
 * Class Response
 * @package Wirecard\PaymentSdk
 */
abstract class Response
{
    /**
     * @var string
     */
    private $rawData;

    /**
     * @var StatusCollection
     */
    private $statusCollection;

    /**
     * Response constructor.
     * @param string $rawData
     * @param StatusCollection $statusCollection
     */
    public function __construct($rawData, StatusCollection $statusCollection)
    {
        $this->rawData = $rawData;
        $this->statusCollection = $statusCollection;
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
     * @return StatusCollection
     */
    public function getStatusCollection()
    {
        return $this->statusCollection;
    }
}
