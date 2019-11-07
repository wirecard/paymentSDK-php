<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity\Payload;

/**
 * Class NvpPayloadData
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
class NvpPayloadData implements PayloadDataInterface
{
    const TYPE = 'nvp';

    /**
     * @var array
     */
    private $payload;

    /**
     * NvpPayloadData constructor.
     * @param array $payload
     * @since 4.0.0
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     * @since 4.0.0
     */
    public function getData()
    {
        return $this->payload;
    }

    /**
     * @return string
     * @since 4.0.0
     */
    public function getType()
    {
        return self::TYPE;
    }
}
