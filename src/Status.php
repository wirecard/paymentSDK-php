<?php

namespace Wirecard\PaymentSdk;

class Status
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $severity;

    /**
     * Error constructor.
     * @param string $code
     * @param string $description
     * @param string $severity
     */
    public function __construct($code, $description, $severity)
    {
        $this->code = $code;
        $this->description = $description;
        $this->severity = $severity;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }
}
