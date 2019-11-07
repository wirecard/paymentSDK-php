<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Mapper\Response;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\Payload\IdealPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\NvpPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\PayloadDataInterface;
use Wirecard\PaymentSdk\Entity\Payload\PayPalPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\RatepayPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\SyncPayloadData;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class MapperFactory
 * @package Wirecard\PaymentSdk\Mapper
 * @since 4.0.0
 */
class MapperFactory
{
    /**
     * @var array
     */
    private $payload;

    /**
     * @var Config
     */
    private $config;

    /**
     * ResponseMapperFactory constructor.
     * @param PayloadDataInterface $payload
     * @param Config $config
     * @since 4.0.0
     */
    public function __construct(PayloadDataInterface $payload, Config $config)
    {
        $this->payload = $payload;
        $this->config = $config;
    }

    /**
     * @return MapperInterface
     * @since 4.0.0
     */
    public function create()
    {
        switch ($this->payload->getType()) {
            case NvpPayloadData::TYPE:
                return new SeamlessMapper($this->payload->getData());
                break;
            case PayPalPayloadData::TYPE:
            case RatepayPayloadData::TYPE:
            case SyncPayloadData::TYPE:
                return new WithSignatureMapper($this->payload->getData(), $this->config);
                break;
            case IdealPayloadData::TYPE:
                return new WithoutSignatureMapper($this->payload->getData(), $this->config);
                break;
            default:
                throw new MalformedResponseException('Missing response in payload.');
                break;
        }
    }
}
