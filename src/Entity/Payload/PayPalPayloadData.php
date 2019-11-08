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
use Wirecard\PaymentSdk\Mapper\Response\MapperInterface;
use Wirecard\PaymentSdk\Mapper\Response\WithoutSignatureMapper;

/**
 * Class PayPalPayloadData
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
class PayPalPayloadData implements PayloadDataInterface
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
     * PayPalPayloadData constructor.
     * @param array $payload
     * @param Config $config
     * @since 4.0.0
     */
    public function __construct(array $payload, Config $config)
    {
        if (!$payload[PayloadFields::FIELD_EPP_RESPONSE]) {
            throw new \InvalidArgumentException('The '. PayloadFields::FIELD_EPP_RESPONSE .' is missing in payload');
        }

        $this->payload = $payload[PayloadFields::FIELD_EPP_RESPONSE];
        $this->config = $config;
    }

    /**
     * @return MapperInterface
     * @since 4.0.0
     */
    public function getResponseMapper()
    {
        return new WithoutSignatureMapper($this->payload, $this->config);
    }
}
