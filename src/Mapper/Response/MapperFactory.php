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
     * MapperFactory constructor
     * @param PayloadDataInterface $payload
     * @since 4.0.0
     */
    public function __construct(PayloadDataInterface $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return MapperInterface
     * @since 4.0.0
     */
    public function create()
    {
        return $this->payload->getResponseMapper();
    }
}
