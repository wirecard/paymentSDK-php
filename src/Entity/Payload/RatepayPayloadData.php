<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity\Payload;

use Wirecard\PaymentSdk\Constant\PayloadFields;

/**
 * Class RatepayPayloadData
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
class RatepayPayloadData implements PayloadDataInterface
{
    const TYPE = 'ratepay';

    /**
     * @var string
     */
    private $payload;

    /**
     * RatepayPayloadData constructor.
     * @param array $payload
     * @since 4.0.0
     */
    public function __construct(array $payload)
    {
        if (!$payload[PayloadFields::FIELD_BASE64_PAYLOAD]) {
            throw new \InvalidArgumentException('The '. PayloadFields::FIELD_BASE64_PAYLOAD .' is missing in payload');
        }

        $this->payload = $payload[PayloadFields::FIELD_BASE64_PAYLOAD];
    }

    /**
     * @return string
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
