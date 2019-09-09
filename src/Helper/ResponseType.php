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
 * @since 3.9.0
 */
class ResponseType
{
    /**
     * @param array $payload
     * @return boolean
     * @since 3.9.0
     */
    public static function isIdealResponse($payload)
    {
        return array_key_exists('ec', $payload) &&
            array_key_exists('trxid', $payload) &&
            array_key_exists('request_id', $payload);
    }
    /**
     * @param array $payload
     * @return boolean
     * @since 3.9.0
     */
    public static function isPaypalResponse($payload)
    {
        return array_key_exists('eppresponse', $payload);
    }
    /**
     * @param array $payload
     * @return boolean
     * @since 3.9.0
     */
    public static function isRatepayResponse($payload)
    {
        return array_key_exists('base64payload', $payload) &&
            array_key_exists('psp_name', $payload);
    }
    /**
     * @param array $payload
     * @return boolean
     * @since 3.9.0
     */
    public static function isSyncResponse($payload)
    {
        return array_key_exists('sync_response', $payload);
    }
    /**
     * @param array $payload
     * @return boolean
     * @since 3.9.0
     */
    public static function isNvpResponse($payload)
    {
        return array_key_exists('response_signature_v2', $payload);
    }
}
