<?php

namespace Wirecard\PaymentSdk\Helper;

/**
 * Class ResponseType
 * @package Wirecard\PaymentSdk\Helper
 * @since 3.9.0
 */
class ResponseType {
    /** @var array */
    private $payload;

    /**
     * ResponseType constructor.
     *
     * @param array $payload
     * @since 3.9.0
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return boolean
     * @since 3.9.0
     */
    public function isIdealResponse() {
        return array_key_exists('ec', $this->payload) &&
            array_key_exists('trxid', $this->payload) &&
            array_key_exists('request_id', $this->payload);
    }

    /**
     * @return boolean
     * @since 3.9.0
     */
    public function isPaypalResponse() {
        return array_key_exists('eppresponse', $this->payload);
    }

    /**
     * @return boolean
     * @since 3.9.0
     */
    public function isRatepayResponse() {
        return array_key_exists('base64payload', $this->payload) &&
            array_key_exists('psp_name', $this->payload);
    }


    /**
     * @return boolean
     * @since 3.9.0
     */
    public function isSyncResponse() {
        return array_key_exists('sync_response', $this->payload);
    }

    /**
     * @return boolean
     * @since 3.9.0
     */
    public function isNvpResponse() {
        return array_key_exists('response_signature_v2', $this->payload);
    }
}