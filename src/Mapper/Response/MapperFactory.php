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
use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class MapperFactory
 * @package Wirecard\PaymentSdk\Mapper
 * @since 4.0.0
 */
class MapperFactory
{
    const FIELD_EC = 'ec';
    const FIELD_TRXID = 'trxid';
    const FIELD_REQUEST_ID = 'request_id';
    const FIELD_EPP_RESPONSE = 'eppresponse';
    const FIELD_BASE64_PAYLOAD = 'base64payload';
    const FIELD_PSP_NAME = 'psp_name';
    const FIELD_SYNC_RESPONSE = 'sync_response';
    const FIELD_RESPONSE_SIGNATURE = 'response_signature_v2';

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
     */
    public function __construct(array $payload, Config $config)
    {
        $this->payload = $payload;
        $this->config = $config;
    }

    /**
     * @since 4.0.0
     * @throws MalformedResponseException
     * @return MapperInterface
     */
    public function create()
    {
        switch (true) {
            case $this->isNvpResponse():
                return new SeamlessMapper($this->payload);
                break;
            case $this->isPayPalResponse():
            case $this->isRatepayResponse():
            case $this->isSyncResponse():
                return new WithSignatureMapper();
                break;
            case $this->isIdealResponse():
                return new WithoutSignatureMapper();
                break;
            default:
                throw new MalformedResponseException('Missing response in payload.');
                break;
        }
    }

    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isIdealResponse()
    {
        return array_key_exists(self::FIELD_EC, $this->payload) &&
            array_key_exists(self::FIELD_TRXID, $this->payload) &&
            array_key_exists(self::FIELD_REQUEST_ID, $this->payload);
    }
    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isPayPalResponse()
    {
        return array_key_exists(self::FIELD_EPP_RESPONSE, $this->payload);
    }
    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isRatepayResponse()
    {
        return array_key_exists(self::FIELD_BASE64_PAYLOAD, $this->payload) &&
            array_key_exists(self::FIELD_PSP_NAME, $this->payload);
    }
    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isSyncResponse()
    {
        return array_key_exists(self::FIELD_SYNC_RESPONSE, $this->payload);
    }
    /**
     * @return boolean
     * @since 4.0.0
     */
    private function isNvpResponse()
    {
        return array_key_exists(self::FIELD_RESPONSE_SIGNATURE, $this->payload);
    }
}
