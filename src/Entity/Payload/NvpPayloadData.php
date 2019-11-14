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
use Wirecard\PaymentSdk\Exception\MalformedPayloadException;
use Wirecard\PaymentSdk\Mapper\Response\SeamlessMapper;

/**
 * Class NvpPayloadData
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
class NvpPayloadData implements PayloadDataInterface
{
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
        if (!array_key_exists(PayloadFields::FIELD_RESPONSE_SIGNATURE, $payload) ||
            !$payload[PayloadFields::FIELD_RESPONSE_SIGNATURE]) {
            throw new MalformedPayloadException(
                'The '. PayloadFields::FIELD_RESPONSE_SIGNATURE .' is missing in payload'
            );
        }

        $this->payload = $payload;
    }

    /**
     * @return string
     * @since 4.0.0
     */
    public function getResponseMapper()
    {
        return new SeamlessMapper($this->payload);
    }
}
