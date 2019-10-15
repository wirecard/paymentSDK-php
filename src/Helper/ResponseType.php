<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */


namespace Wirecard\PaymentSdk\Helper;

/**
 * Class ResponseType
 * @package Wirecard\PaymentSdk\Helper
 * @since 4.0.0
 */
class ResponseType
{
    const FIELD_EC = 'ec';
    const FIELD_TRXID = 'trxid';
    const FIELD_REQUEST_ID = 'request_id';
    const FIELD_EPP_RESPONSE = 'eppresponse';
    const FIELD_BASE64_PAYLOAD = 'base_64_payload';
    const FIELD_PSP_NAME = 'psp_name';
    const FIELD_SYNC_RESPONSE = 'sync_response';
    const FIELD_RESPONSE_SIGNATURE = 'response_signature_v2';

    /**
     * @param array $payload
     * @return boolean
     * @since 4.0.0
     */
    public static function isIdealResponse($payload)
    {
        return array_key_exists(self::FIELD_EC, $payload) &&
            array_key_exists(self::FIELD_TRXID, $payload) &&
            array_key_exists(self::FIELD_REQUEST_ID, $payload);
    }
    /**
     * @param array $payload
     * @return boolean
     * @since 4.0.0
     */
    public static function isPaypalResponse($payload)
    {
        return array_key_exists(self::FIELD_EPP_RESPONSE, $payload);
    }
    /**
     * @param array $payload
     * @return boolean
     * @since 4.0.0
     */
    public static function isRatepayResponse($payload)
    {
        return array_key_exists(self::FIELD_BASE64_PAYLOAD, $payload) &&
            array_key_exists(self::FIELD_PSP_NAME, $payload);
    }
    /**
     * @param array $payload
     * @return boolean
     * @since 4.0.0
     */
    public static function isSyncResponse($payload)
    {
        return array_key_exists(self::FIELD_SYNC_RESPONSE, $payload);
    }
    /**
     * @param array $payload
     * @return boolean
     * @since 4.0.0
     */
    public static function isNvpResponse($payload)
    {
        return array_key_exists(self::FIELD_RESPONSE_SIGNATURE, $payload);
    }
}
