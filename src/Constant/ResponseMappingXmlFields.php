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
 * Contains mappable fields for the XML we build
 *
 * Class ResponseMappingXmlFields
 * @package Wirecard\PaymentSdk\Constant
 * @since 4.0.0
 */
class ResponseMappingXmlFields
{
    const PAYMENT = 'payment';
    const MERCHANT_ACCOUNT_ID = 'merchant-account-id';
    const TRANSACTION_ID = 'transaction-id';
    const TRANSACTION_STATE = 'transaction-state';
    const TRANSACTION_TYPE = 'transaction-type';
    const PAYMENT_METHOD = 'payment-method';
    const REQUEST_ID = 'request-id';

    const REQUESTED_AMOUNT_CURRENCY = 'currency';

    const ACS_URL = 'acs-url';
    const PAREQ = 'pareq';
    const CARDHOLDER_AUTHENTICATION_STATUS = 'cardholder-authentication-status';

    const TOKEN_ID = 'token-id';
    const MASKED_ACCOUNT_NUMBER = 'masked-account-number';

    const PARENT_TRANSACTION_ID = 'parent-transaction-id';
    const REQUESTED_AMOUNT = 'requested-amount';
    const THREE_D = 'three-d';
    const STATUSES = 'statuses';
    const CARD_TOKEN = 'card-token';
    const STATUS = 'status';
}
