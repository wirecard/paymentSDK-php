<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Mapper\Response;

use PHPUnit_Framework_TestCase;
use Wirecard\PaymentSdk\Mapper\Response\SeamlessMapper;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;

class SeamlessMapperUTest extends PHPUnit_Framework_TestCase
{
    const EXPECTED_CARD_TYPE = 'visa';
    const EXPECTED_EXPIRATION_YEAR = 2023;
    const EXPECTED_EXPIRATION_MONTH = 1;
    const EXPECTED_NULL = null;

    const FIELD_CARD_TYPE = 'card-type';
    const FIELD_EXPIRATION_YEAR = 'expiration-year';
    const FIELD_EXPIRATION_MONTH = 'expiration-month';

    const PAYLOAD_FIELD_CARD_TYPE = 'card_type';
    const PAYLOAD_FIELD_EXPIRATION_YEAR = 'expiration_year';
    const PAYLOAD_FIELD_EXPIRATION_MONTH = 'expiration_month';

    /**
     * @dataProvider optionalCreditCardDataProvider
     * @param $payloadData
     * @param $expected
     * @param $expectedType
     */
    public function testOptionalCreditCardData($payloadData, $expected, $expectedType)
    {
        $current = $this->getResultField($expectedType, $payloadData);
        $this->assertEquals($expected, $current);
    }

    /**
     * @return array
     */
    public function optionalCreditCardDataProvider()
    {
        return [
            [$this->payloadData(), self::EXPECTED_CARD_TYPE, self::FIELD_CARD_TYPE],
            [$this->payloadData(), self::EXPECTED_EXPIRATION_YEAR, self::FIELD_EXPIRATION_YEAR],
            [$this->payloadData(), self::EXPECTED_EXPIRATION_MONTH, self::FIELD_EXPIRATION_MONTH],

            [$this->getPayloadWithoutYear(), self::EXPECTED_CARD_TYPE, self::FIELD_CARD_TYPE],
            [$this->getPayloadWithoutYear(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_YEAR],
            [$this->getPayloadWithoutYear(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_MONTH],

            [$this->getPayloadWithoutMonth(), self::EXPECTED_CARD_TYPE, self::FIELD_CARD_TYPE],
            [$this->getPayloadWithoutMonth(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_YEAR],
            [$this->getPayloadWithoutMonth(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_MONTH],

            [$this->getPayloadWithoutMonthAndYear(), self::EXPECTED_CARD_TYPE, self::FIELD_CARD_TYPE],
            [$this->getPayloadWithoutMonthAndYear(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_YEAR],
            [$this->getPayloadWithoutMonthAndYear(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_MONTH],

            [$this->getPayloadWithoutCardType(), self::EXPECTED_NULL, self::FIELD_CARD_TYPE],
            [$this->getPayloadWithoutCardType(), self::EXPECTED_EXPIRATION_YEAR, self::FIELD_EXPIRATION_YEAR],
            [$this->getPayloadWithoutCardType(), self::EXPECTED_EXPIRATION_MONTH, self::FIELD_EXPIRATION_MONTH],

            [$this->getPayloadWithoutYearAndCardType(), self::EXPECTED_NULL, self::FIELD_CARD_TYPE],
            [$this->getPayloadWithoutYearAndCardType(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_YEAR],
            [$this->getPayloadWithoutYearAndCardType(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_MONTH],

            [$this->getPayloadWithoutMonthAndCardType(), self::EXPECTED_NULL, self::FIELD_CARD_TYPE],
            [$this->getPayloadWithoutMonthAndCardType(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_YEAR],
            [$this->getPayloadWithoutMonthAndCardType(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_MONTH],

            [$this->getPayloadWithoutOptionalData(), self::EXPECTED_NULL, self::FIELD_CARD_TYPE],
            [$this->getPayloadWithoutOptionalData(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_YEAR],
            [$this->getPayloadWithoutOptionalData(), self::EXPECTED_NULL, self::FIELD_EXPIRATION_MONTH],
        ];
    }
    /**
     * @param $name
     * @param FormInteractionResponse $result
     * @return mixed|null
     */
    private function getResultField($name, FormInteractionResponse $result)
    {
        $card = (array)$result->getCard()->mappedProperties();
        $cardArray = (array)$card;
        if (isset($cardArray[$name])) {
            $value = $cardArray[$name];
            $value = end($value);
            return $value;
        }
        return null;
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function getPayloadWithoutYear()
    {
        $payload = $this->payloadArray();
        unset($payload[self::PAYLOAD_FIELD_EXPIRATION_YEAR]);
        return $this->getSeamlessMapper($payload);
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function getPayloadWithoutMonth()
    {
        $payload = $this->payloadArray();
        unset($payload[self::PAYLOAD_FIELD_EXPIRATION_MONTH]);
        return $this->getSeamlessMapper($payload);
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function getPayloadWithoutMonthAndYear()
    {
        $payload = $this->payloadArray();
        unset($payload[self::PAYLOAD_FIELD_EXPIRATION_YEAR]);
        unset($payload[self::PAYLOAD_FIELD_EXPIRATION_MONTH]);
        return $this->getSeamlessMapper($payload);
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function getPayloadWithoutCardType()
    {
        $payload = $this->payloadArray();
        unset($payload[self::PAYLOAD_FIELD_CARD_TYPE]);
        return $this->getSeamlessMapper($payload);
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function getPayloadWithoutYearAndCardType()
    {
        $payload = $this->payloadArray();
        unset($payload[self::PAYLOAD_FIELD_CARD_TYPE]);
        unset($payload[self::PAYLOAD_FIELD_EXPIRATION_YEAR]);
        return $this->getSeamlessMapper($payload);
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function getPayloadWithoutMonthAndCardType()
    {
        $payload = $this->payloadArray();
        unset($payload[self::PAYLOAD_FIELD_CARD_TYPE]);
        unset($payload[self::PAYLOAD_FIELD_EXPIRATION_MONTH]);
        return $this->getSeamlessMapper($payload);
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function getPayloadWithoutOptionalData()
    {
        $payload = $this->payloadArray();
        unset($payload[self::PAYLOAD_FIELD_CARD_TYPE]);
        unset($payload[self::PAYLOAD_FIELD_EXPIRATION_YEAR]);
        unset($payload[self::PAYLOAD_FIELD_EXPIRATION_MONTH]);
        return $this->getSeamlessMapper($payload);
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function payloadData()
    {
        return $this->getSeamlessMapper($this->payloadArray());
    }

    /**
     * @param $payload
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function getSeamlessMapper($payload)
    {
        $seamlessMapper = new SeamlessMapper($payload);
        /** @var FormInteractionResponse $result */
        return $seamlessMapper->map();
    }

    /**
     * @return array
     */
    private function payloadArray()
    {
        $json = json_decode($this->payloadJsonString(), true);
        return $json['payload'];
    }

    /**
     * @return string
     */
    private function payloadJsonString()
    {
        return '{
        "payload" : {
            "country" : "AT",
            "notification_url_1" : "https://api-wdcee-test.wirecard.com:443' .
            '/engine/rest/hpp/acs/e580a945-d353-4641-9683-b778b7cacb5f/",
            "response_signature" : "9a5856505da1efc86e50f56cafcdcd6b81b4ee2df845b86c742d1b33862958da",
            "provider_status_code_1" : "0",
            "response_signature_v2" : "SFMyNTYKdHJhbnNhY3Rpb25faWQ9ZTU4MGE5NDUtZDM1My00NjQxLTk2ODMtYj' .
            'c3OGI3Y2FjYjVmCmNvbXBsZXRpb25fdGltZXN0YW1wPTIwMjAwMTIxMTE1MjA3Cm1hc2tlZF9hY2NvdW50X251bW' .
            'Jlcj00MDEyMDAqKioqKioxMDAzCnRva2VuX2lkPTQzOTc0MjM0NzYxMDEwMDMKYXV0aG9yaXphdGlvbl9jb2RlPQ' .
            'ptZXJjaGFudF9hY2NvdW50X2lkPTQ5ZWUxMzU1LWNkZDMtNDIwNS05MjBmLTg1MzkxYmIzODY1ZAp0cmFuc2FjdG' .
            'lvbl9zdGF0ZT1zdWNjZXNzCmlwX2FkZHJlc3M9MTcyLjIyLjAuMQp0cmFuc2FjdGlvbl90eXBlPXB1cmNoYXNlCn' .
            'JlcXVlc3RfaWQ9MzAzYmE3OGZiZDVkNmNjMzNhNzMxMWFlNDg1ODYyZDc4OTAxNzg5YWE1ZTM3ZWJlM2YyNzI5Yz' .
            'ljNGE1NTAxYwo=.zgD/W3kwQRSrkOeWAIedKIYvuv5Z7hI61gdJIxQjq/o=",
            "provider_status_code_2" : "0",
            "browser_hostname" : "10.137.8.253",
            "provider_status_description_2" : "Transaction OK",
            "merchant_crm_id" : "3",
            "completion_time_stamp" : "20200121115207",
            "provider_status_description_1" : "Transaction OK",
            "token_id" : "4397423476101003",
            "browser_version" : "72.0",
            "browser_os" : "Mac OS X",
            "payment_method" : "creditcard",
            "cardholder_authentication_status" : "Y",
            "transaction_id" : "e580a945-d353-4641-9683-b778b7cacb5f",
            "transaction_type" : "purchase",
            "status_code_1" : "201.0000",
            "status_code_2" : "200.1077",
            "status_description_1" : "3d-acquirer:The resource was successfully created.",
            "phone" : "123123123",
            "status_description_2" : "3d-acquirer:Card is eligible for the ACS authentication process. ' .
            'If the customer has not yet activated his card for 3-D Secure processing his issuer may ' .
            'offer activation during shopping.",
            "parent_transaction_id" : "d6de377c-4382-465a-b728-cef6bd89e0ef",
            "city" : "city",
            "requested_amount" : "104.4",
            "nonce3d" : "9A43BD599FD6585D886B1F084E20CDBC",
            "pareq" : "eJxtU8tuo0AQvOcrEPdlHgwva5goWctZH7JKNjEr+YaGViAy4AwQ23+/DbaTtQEJie6uoatrquXtvtx' .
            'Yn2Caoq5imznUtqDSdVZUb7G9el38CG2radMqSzd1BbF9gMa+VTfyNTcA8xfQnQF1Y+EjH6Fp0jewiiy2hRcx1/co' .
            'DRkXQtDIj3z7iBuwT3d/4OM7HnInFgpJOFySc3gJegSj87RqL9NDKdUf98vfinFXeL4kp3CMK8Es5wq5UYYvleSYG' .
            'AOrtAT1tzCgU5NZcyhr6yWvt5IMhTFe113VmoMKObY/B2NYZzYqb9vtjJDdbufsTg0cXZeS9MXLicn0yPKp69PNFJ' .
            'F9kSldJp/ZQ9KtkvU6fVgvE7rwk0W+WL0/x5L0iPG5LG1BccpRGc4sxmcen1GcZchPCF72MypGhSNQ72M0hm1PRO+' .
            '+4AJFv8pOqNkZg1Y8qCgIUc5zNAbCfovWxH+gab6+rzScFkv+/DXpI92iH6jLIsGYH4Su5wYeD33hBy4aOqRB764B' .
            'NMmmwGvnLmUDneLaA5L83xWpfa9Cf9fDDuGCkcsN+wcH3ugm",
            "merchant_account_id" : "49ee1355-cdd3-4205-920f-85391bb3865d",
            "expiration_month" : "1",
            "street1" : "addtess 12",
            "email" : "test@test.com",
            "status_severity_2" : "information",
            "status_severity_1" : "information",
            "browser_screen_resolution" : "1920x1080",
            "last_name" : "asdasd",
            "ip_address" : "172.22.0.1",
            "card_type" : "visa",
            "browser_ip_address" : "109.73.151.164",
            "acs_url" : "https://c3-test.wirecard.com/acssim/app/bank",
            "expiration_year" : "2023",
            "masked_account_number" : "401200******1003",
            "browser_referrer" : "http://nxclebq83o.presta.eu.ngrok.io/en/order",
            "transaction_state" : "success",
            "requested_amount_currency" : "EUR",
            "postal_code" : "1234",
            "request_id" : "303ba78fbd5d6cc33a7311ae485862d78901789aa5e37ebe3f2729c9c4a5501c"
        },
        "finished" : false,
        "retry-allowed" : false,
        "installment-plan-options" : { },
        "same-page-redirect" : false
    }';
    }
}
