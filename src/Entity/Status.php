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
 * Class Status
 * @package Wirecard\PaymentSdk\Entity
 */
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
