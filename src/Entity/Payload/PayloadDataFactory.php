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
use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class PayloadDataFactory
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
class PayloadDataFactory
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
     * PayloadDataFactory constructor.
     * @param array $payload
     * @param Config $config
     * @since 4.0.0
     */
    public function __construct(array $payload, Config $config)
    {
        $this->payload = $payload;
        $this->config = $config;
    }

    /**
     * @return PayloadDataInterface
     * @throws \Http\Client\Exception
     * @since 4.0.0
     */
    public function create()
    {
        if ($this->isNvpResponse()) {
            return new NvpPayloadData($this->payload);
        }

        if ($this->isSyncResponse()) {
            return new SyncPayloadData($this->payload, $this->config);
        }

        if ($this->isRatepayResponse()) {
            return new RatepayPayloadData($this->payload, $this->config);
        }

        if ($this->isPayPalResponse()) {
            return new PayPalPayloadData($this->payload, $this->config);
        }

        if ($this->isIdealResponse()) {
            return new IdealPayloadData($this->payload, $this->config);
        }

        throw new MalformedResponseException('The payload cannot be identified.');
    }

    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isIdealResponse()
    {
        return array_key_exists(PayloadFields::FIELD_EC, $this->payload) &&
            array_key_exists(PayloadFields::FIELD_TRXID, $this->payload) &&
            array_key_exists(PayloadFields::FIELD_REQUEST_ID, $this->payload);
    }

    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isPayPalResponse()
    {
        return array_key_exists(PayloadFields::FIELD_EPP_RESPONSE, $this->payload);
    }

    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isRatepayResponse()
    {
        return array_key_exists(PayloadFields::FIELD_BASE64_PAYLOAD, $this->payload) &&
            array_key_exists(PayloadFields::FIELD_PSP_NAME, $this->payload);
    }

    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isSyncResponse()
    {
        return array_key_exists(PayloadFields::FIELD_SYNC_RESPONSE, $this->payload);
    }

    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isNvpResponse()
    {
        return array_key_exists(PayloadFields::FIELD_RESPONSE_SIGNATURE, $this->payload);
    }
}
