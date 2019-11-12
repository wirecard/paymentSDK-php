<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity\Payload;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Constant\PayloadFields;
use Wirecard\PaymentSdk\Exception\MalformedPayloadException;
use Wirecard\PaymentSdk\Mapper\Response\MapperInterface;
use Wirecard\PaymentSdk\Mapper\Response\WithSignatureMapper;

/**
 * Class SyncPayloadData
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
class SyncPayloadData implements PayloadDataInterface
{
    /**
     * @var string
     */
    private $payload;

    /**
     * @var Config
     */
    private $config;

    /**
     * SyncPayloadData constructor.
     * @param array $payload
     * @param Config $config
     * @since 4.0.0
     */
    public function __construct(array $payload, Config $config)
    {
        if (!array_key_exists(PayloadFields::FIELD_SYNC_RESPONSE, $payload) ||
            !$payload[PayloadFields::FIELD_SYNC_RESPONSE]) {
            throw new MalformedPayloadException('The '. PayloadFields::FIELD_SYNC_RESPONSE .' is missing in payload');
        }

        $this->payload = $payload[PayloadFields::FIELD_SYNC_RESPONSE];
        $this->config = $config;
    }

    /**
     * @return MapperInterface
     * @since 4.0.0
     */
    public function getResponseMapper()
    {
        return new WithSignatureMapper($this->payload, $this->config);
    }
}
