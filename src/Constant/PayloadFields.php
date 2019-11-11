<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Constant;

/**
 * Here are field key which we receive in the payload
 *
 * Class PayloadFields
 * @package Wirecard\PaymentSdk\Constant
 * @since 4.0.0
 */
class PayloadFields
{
    const FIELD_EC = 'ec';
    const FIELD_TRXID = 'trxid';

    const FIELD_PSP_NAME = 'psp_name';
    const FIELD_SYNC_RESPONSE = 'sync_response';
    const FIELD_RESPONSE_SIGNATURE = 'response_signature_v2';
    const FIELD_BASE64_PAYLOAD = 'base64payload';
    const FIELD_REQUEST_ID = 'request_id';
    const FIELD_EPP_RESPONSE = 'eppresponse';
}
