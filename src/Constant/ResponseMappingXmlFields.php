<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Constant;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;

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
    const PAYMENT_METHODS = 'payment-methods';
    const PAYMENT_METHOD = 'payment-method';
    const REQUEST_ID = 'request-id';

    const REQUESTED_AMOUNT_CURRENCY = 'currency';
    const PAYMENT_METHOD_NAME = 'name';

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

    /**
     * Return array of account holder fields
     * @return array
     * @since 4.0.3
     */
    public function getAccountHolderFields()
    {
        return [
            AccountHolder::KEY_FIRST_NAME             => self::ACCOUNT_HOLDER_FIRST_NAME,
            AccountHolder::KEY_LAST_NAME              => self::ACCOUNT_HOLDER_LAST_NAME,
            AccountHolder::KEY_EMAIL                  => self::ACCOUNT_HOLDER_EMAIL,
            AccountHolder::KEY_PHONE                  => self::ACCOUNT_HOLDER_PHONE,
            AccountHolder::KEY_MOBILE_PHONE           => self::ACCOUNT_HOLDER_MOBILE_PHONE,
            AccountHolder::KEY_WORK_PHONE             => self::ACCOUNT_HOLDER_WORK_PHONE,
            AccountHolder::KEY_DATE_OF_BIRTH          => self::ACCOUNT_HOLDER_DATE_OF_BIRTH,
            AccountHolder::KEY_CRM_ID                 => self::ACCOUNT_HOLDER_CRM_ID,
            AccountHolder::KEY_GENDER                 => self::ACCOUNT_HOLDER_GENDER,
            AccountHolder::KEY_SHIPPING_METHOD        => self::ACCOUNT_HOLDER_SHIPPING_METHOD,
            AccountHolder::KEY_SOCIAL_SECURITY_NUMBER => self::ACCOUNT_HOLDER_SOCIAL_SECURITY_NUMBER,
        ];
    }

    /**
     * Return array of account holder address fields
     * @return array
     * @since 4.0.3
     */
    public function getAccountHolderAddressFields()
    {
        return [
            Address::KEY_COUNTRY         => self::ACCOUNT_HOLDER_COUNTRY,
            Address::KEY_STATE           => self::ACCOUNT_HOLDER_STATE,
            Address::KEY_CITY            => self::ACCOUNT_HOLDER_CITY,
            Address::KEY_STREET_1        => self::ACCOUNT_HOLDER_STREET_1,
            Address::KEY_STREET_2        => self::ACCOUNT_HOLDER_STREET_2,
            Address::KEY_STREET_3        => self::ACCOUNT_HOLDER_STREET_3,
            Address::KEY_POSTAL_CODE     => self::ACCOUNT_HOLDER_POSTAL_CODE,
            Address::KEY_HOUSE_EXTENSION => self::ACCOUNT_HOLDER_HOUSE_EXTENSION,
        ];
    }
}
