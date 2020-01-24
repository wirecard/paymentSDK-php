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
 * Contains mappable fields for the seamless form
 *
 * Class SeamlessFields
 * @package Wirecard\PaymentSdk\Constant
 * @since 4.0.0
 */
class SeamlessFields
{
    const MERCHANT_ACCOUNT_ID = 'merchant_account_id';
    const TRANSACTION_ID = 'transaction_id';
    const TRANSACTION_STATE = 'transaction_state';
    const TRANSACTION_TYPE = 'transaction_type';
    const PAYMENT_METHOD = 'payment_method';
    const REQUEST_ID = 'request_id';
    const NONCE3D = 'nonce3d';

    const REQUESTED_AMOUNT = 'requested_amount';
    const REQUESTED_AMOUNT_CURRENCY = 'requested_amount_currency';

    const ACS_URL = 'acs_url';
    const PAREQ = 'pareq';
    const CARDHOLDER_AUTHENTICATION_STATUS = 'cardholder_authentication_status';

    const PARENT_TRANSACTION_ID = 'parent_transaction_id';

    const TOKEN_ID = 'token_id';
    const MASKED_ACCOUNT_NUMBER = 'masked_account_number';

    const PROCESSING_URL = 'notification_url_1';
}
