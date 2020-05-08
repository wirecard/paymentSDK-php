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

    const CARD_TYPE = 'card-type';
    const EXPIRATION_MONTH = 'expiration-month';
    const EXPIRATION_YEAR = 'expiration-year';
    const TOKEN_ID = 'token-id';
    const MASKED_ACCOUNT_NUMBER = 'masked-account-number';

    const PARENT_TRANSACTION_ID = 'parent-transaction-id';
    const REQUESTED_AMOUNT = 'requested-amount';
    const THREE_D = 'three-d';
    const STATUSES = 'statuses';
    const CARD_TOKEN = 'card-token';
    const CARD = 'card';
    const STATUS = 'status';

    const ACCOUNT_HOLDER = 'account-holder';
    const ACCOUNT_HOLDER_ADDRESS = 'address';
    const ACCOUNT_HOLDER_FIRST_NAME = 'first-name';
    const ACCOUNT_HOLDER_LAST_NAME = 'last-name';
    const ACCOUNT_HOLDER_EMAIL = 'email';
    const ACCOUNT_HOLDER_PHONE = 'phone';
    const ACCOUNT_HOLDER_MOBILE_PHONE = 'mobile-phone';
    const ACCOUNT_HOLDER_WORK_PHONE = 'work-phone';
    const ACCOUNT_HOLDER_DATE_OF_BIRTH = 'date-of-birth';
    const ACCOUNT_HOLDER_CRM_ID = 'crm-id';
    const ACCOUNT_HOLDER_GENDER = 'gender';
    const ACCOUNT_HOLDER_SHIPPING_METHOD = 'shipping-method';
    const ACCOUNT_HOLDER_SOCIAL_SECURITY_NUMBER = 'social-security-number';
    const ACCOUNT_HOLDER_COUNTRY = 'country';
    const ACCOUNT_HOLDER_STATE = 'state';
    const ACCOUNT_HOLDER_CITY = 'city';
    const ACCOUNT_HOLDER_STREET_1 = 'street1';
    const ACCOUNT_HOLDER_STREET_2 = 'street2';
    const ACCOUNT_HOLDER_STREET_3 = 'street3';
    const ACCOUNT_HOLDER_POSTAL_CODE = 'postal-code';
    const ACCOUNT_HOLDER_HOUSE_EXTENSION = 'house-extension';

    const ACCOUNT_HOLDER_FIELDS = [
        'first_name'             => self::ACCOUNT_HOLDER_FIRST_NAME,
        'last_name'              => self::ACCOUNT_HOLDER_LAST_NAME,
        'email'                  => self::ACCOUNT_HOLDER_EMAIL,
        'phone'                  => self::ACCOUNT_HOLDER_PHONE,
        'mobile_phone'           => self::ACCOUNT_HOLDER_MOBILE_PHONE,
        'work_phone'             => self::ACCOUNT_HOLDER_WORK_PHONE,
        'date_of_birth'          => self::ACCOUNT_HOLDER_DATE_OF_BIRTH,
        'crm_id'                 => self::ACCOUNT_HOLDER_CRM_ID,
        'gender'                 => self::ACCOUNT_HOLDER_GENDER,
        'shipping_method'        => self::ACCOUNT_HOLDER_SHIPPING_METHOD,
        'social_security_number' => self::ACCOUNT_HOLDER_SOCIAL_SECURITY_NUMBER,
    ];

    const ACCOUNT_HOLDER_ADDRESS_FIELDS = [
        'country'         => self::ACCOUNT_HOLDER_COUNTRY,
        'state'           => self::ACCOUNT_HOLDER_STATE,
        'city'            => self::ACCOUNT_HOLDER_CITY,
        'street_1'        => self::ACCOUNT_HOLDER_STREET_1,
        'street_2'        => self::ACCOUNT_HOLDER_STREET_2,
        'street_3'        => self::ACCOUNT_HOLDER_STREET_3,
        'postal_code'     => self::ACCOUNT_HOLDER_POSTAL_CODE,
        'house_extension' => self::ACCOUNT_HOLDER_HOUSE_EXTENSION,
    ];
}
