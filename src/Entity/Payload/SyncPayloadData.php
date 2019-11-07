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
 * Class SyncPayloadData
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
class SyncPayloadData implements PayloadDataInterface
{
    const TYPE = 'sync';

    /**
     * @var string
     */
    private $payload;

    /**
     * SyncPayloadData constructor.
     * @param array $payload
     * @since 4.0.0
     */
    public function __construct(array $payload)
    {
        if (!$payload[PayloadFields::FIELD_SYNC_RESPONSE]) {
            throw new \InvalidArgumentException('The '. PayloadFields::FIELD_SYNC_RESPONSE .' is missing in payload');
        }

        $this->payload = $payload[PayloadFields::FIELD_SYNC_RESPONSE];
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
