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

    const FIELD_CARD_TYPE = 'card-type';
    const FIELD_EXPIRATION_YEAR = 'expiration-year';
    const FIELD_EXPIRATION_MONTH = 'expiration-month';

    const PAYLOAD_FIELD_CARD_TYPE = 'card_type';
    const PAYLOAD_FIELD_EXPIRATION_YEAR = 'expiration_year';
    const PAYLOAD_FIELD_EXPIRATION_MONTH = 'expiration_month';

    /**
     * Test optional credit card data (card type, expiration year and expiration month)
     */
    public function testAllOptionalCreditCardData()
    {
        $result = $this->getPayloadResult($this->payloadData());
        $this->assertEquals(self::EXPECTED_CARD_TYPE, $this->getCardType($result));
        $this->assertEquals(self::EXPECTED_EXPIRATION_YEAR, $this->getExpirationYear($result));
        $this->assertEquals(self::EXPECTED_EXPIRATION_MONTH, $this->getExpirationMonth($result));
    }

    /**
     * Test optional credit card data if expiration year is not included
     */
    public function testOptionalCreditCardDataWithoutYear()
    {
        $result = $this->getPayloadResult($this->getPayloadWithoutYear());
        $this->assertEquals(self::EXPECTED_CARD_TYPE, $this->getCardType($result));
        $this->assertNull($this->getExpirationYear($result));
        $this->assertNull($this->getExpirationMonth($result));
    }

    /**
     * Test optional credit card data if expiration month is not included
     */
    public function testOptionalCreditCardDataWithoutMonth()
    {
        $result = $this->getPayloadResult($this->getPayloadWithoutMonth());
        $this->assertEquals(self::EXPECTED_CARD_TYPE, $this->getCardType($result));
        $this->assertNull($this->getExpirationYear($result));
        $this->assertNull($this->getExpirationMonth($result));
    }

    /**
     * Test optional credit card data if expiration year is not included
     */
    public function testOptionalCreditCardDataWithoutMonthAndYear()
    {
        $result = $this->getPayloadResult($this->getPayloadWithoutMonthAndYear());
        $this->assertEquals(self::EXPECTED_CARD_TYPE, $this->getCardType($result));
        $this->assertNull($this->getExpirationYear($result));
        $this->assertNull($this->getExpirationMonth($result));
    }

    /**
     * Test optional credit card data if card type is not included
     */
    public function testOptionalCreditCardDataWithoutCardType()
    {
        $result = $this->getPayloadResult($this->getPayloadWithoutCardType());
        $this->assertNull($this->getCardType($result));
        $this->assertEquals(self::EXPECTED_EXPIRATION_YEAR, $this->getExpirationYear($result));
        $this->assertEquals(self::EXPECTED_EXPIRATION_MONTH, $this->getExpirationMonth($result));
    }

    /**
     * Test optional credit card data if expiration year and card type are not included
     */
    public function testOptionalCreditCardDataWithoutYearAndCardType()
    {
        $result = $this->getPayloadResult($this->getPayloadWithoutYearAndCardType());
        $this->assertNull($this->getCardType($result));
        $this->assertNull($this->getExpirationYear($result));
        $this->assertNull($this->getExpirationMonth($result));
    }

    /**
     * Test optional credit card data if expiration month and card type are not included
     */
    public function testOptionalCreditCardDataWithoutMonthAndCardType()
    {
        $result = $this->getPayloadResult($this->getPayloadWithoutMonthAndCardType());
        $this->assertNull($this->getCardType($result));
        $this->assertNull($this->getExpirationYear($result));
        $this->assertNull($this->getExpirationMonth($result));
    }

    /**
     * Test optional credit card data if optional data is not included
     */
    public function testOptionalCreditCardDataWithoutOptionalData()
    {
        $result = $this->getPayloadResult($this->getPayloadWithoutOptionalData());
        $this->assertNull($this->getCardType($result));
        $this->assertNull($this->getExpirationYear($result));
        $this->assertNull($this->getExpirationMonth($result));
    }

    /**
     * @param array $payload
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     */
    private function getPayloadResult(array $payload)
    {
        $seamlessMapper = new SeamlessMapper($payload);
        /** @var FormInteractionResponse $result */
        return $seamlessMapper->map();
    }

    /**
     * @param FormInteractionResponse $result
     * @return mixed|null
     */
    private function getCardType(FormInteractionResponse $result)
    {
        return $this->getResultField(self::FIELD_CARD_TYPE, $result);
    }

    /**
     * @param FormInteractionResponse $result
     * @return mixed|null
     */
    private function getExpirationYear(FormInteractionResponse $result)
    {
        return $this->getResultField(self::FIELD_EXPIRATION_YEAR, $result);
    }

    /**
     * @param FormInteractionResponse $result
     * @return mixed|null
     */
    private function getExpirationMonth(FormInteractionResponse $result)
    {
        return $this->getResultField(self::FIELD_EXPIRATION_MONTH, $result);
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
     * @return array
     */
    private function getPayloadWithoutYear()
    {
        $data = $this->payloadData();
        unset($data[self::PAYLOAD_FIELD_EXPIRATION_YEAR]);
        return $data;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutMonth()
    {
        $data = $this->payloadData();
        unset($data[self::PAYLOAD_FIELD_EXPIRATION_MONTH]);
        return $data;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutMonthAndYear()
    {
        $data = $this->payloadData();
        unset($data[self::PAYLOAD_FIELD_EXPIRATION_YEAR]);
        unset($data[self::PAYLOAD_FIELD_EXPIRATION_MONTH]);
        return $data;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutCardType()
    {
        $data = $this->payloadData();
        unset($data[self::PAYLOAD_FIELD_CARD_TYPE]);
        return $data;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutYearAndCardType()
    {
        $data = $this->payloadData();
        unset($data[self::PAYLOAD_FIELD_CARD_TYPE]);
        unset($data[self::PAYLOAD_FIELD_EXPIRATION_YEAR]);
        return $data;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutMonthAndCardType()
    {
        $data = $this->payloadData();
        unset($data[self::PAYLOAD_FIELD_CARD_TYPE]);
        unset($data[self::PAYLOAD_FIELD_EXPIRATION_MONTH]);
        return $data;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutOptionalData()
    {
        $data = $this->payloadData();
        unset($data[self::PAYLOAD_FIELD_CARD_TYPE]);
        unset($data[self::PAYLOAD_FIELD_EXPIRATION_YEAR]);
        unset($data[self::PAYLOAD_FIELD_EXPIRATION_MONTH]);
        return $data;
    }

    /**
     * @return array
     */
    private function payloadData()
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
            "notification_url_1" : "https://api-wdcee-test.wirecard.com:443/engine/rest/hpp/acs/e580a945-d353-4641-9683-b778b7cacb5f/",
            "response_signature" : "9a5856505da1efc86e50f56cafcdcd6b81b4ee2df845b86c742d1b33862958da",
            "provider_status_code_1" : "0",
            "response_signature_v2" : "SFMyNTYKdHJhbnNhY3Rpb25faWQ9ZTU4MGE5NDUtZDM1My00NjQxLTk2ODMtYjc3OGI3Y2FjYjVmCmNvbXBsZXRpb25fdGltZXN0YW1wPTIwMjAwMTIxMTE1MjA3Cm1hc2tlZF9hY2NvdW50X251bWJlcj00MDEyMDAqKioqKioxMDAzCnRva2VuX2lkPTQzOTc0MjM0NzYxMDEwMDMKYXV0aG9yaXphdGlvbl9jb2RlPQptZXJjaGFudF9hY2NvdW50X2lkPTQ5ZWUxMzU1LWNkZDMtNDIwNS05MjBmLTg1MzkxYmIzODY1ZAp0cmFuc2FjdGlvbl9zdGF0ZT1zdWNjZXNzCmlwX2FkZHJlc3M9MTcyLjIyLjAuMQp0cmFuc2FjdGlvbl90eXBlPXB1cmNoYXNlCnJlcXVlc3RfaWQ9MzAzYmE3OGZiZDVkNmNjMzNhNzMxMWFlNDg1ODYyZDc4OTAxNzg5YWE1ZTM3ZWJlM2YyNzI5YzljNGE1NTAxYwo=.zgD/W3kwQRSrkOeWAIedKIYvuv5Z7hI61gdJIxQjq/o=",
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
            "status_description_2" : "3d-acquirer:Card is eligible for the ACS authentication process. If the customer has not yet activated his card for 3-D Secure processing his issuer may offer activation during shopping.",
            "parent_transaction_id" : "d6de377c-4382-465a-b728-cef6bd89e0ef",
            "city" : "city",
            "requested_amount" : "104.4",
            "nonce3d" : "9A43BD599FD6585D886B1F084E20CDBC",
            "pareq" : "eJxtU8tuo0AQvOcrEPdlHgwva5goWctZH7JKNjEr+YaGViAy4AwQ23+/DbaTtQEJie6uoatrquXtvtxYn2Caoq5imznUtqDSdVZUb7G9el38CG2radMqSzd1BbF9gMa+VTfyNTcA8xfQnQF1Y+EjH6Fp0jewiiy2hRcx1/coDRkXQtDIj3z7iBuwT3d/4OM7HnInFgpJOFySc3gJegSj87RqL9NDKdUf98vfinFXeL4kp3CMK8Es5wq5UYYvleSYGAOrtAT1tzCgU5NZcyhr6yWvt5IMhTFe113VmoMKObY/B2NYZzYqb9vtjJDdbufsTg0cXZeS9MXLicn0yPKp69PNFJF9kSldJp/ZQ9KtkvU6fVgvE7rwk0W+WL0/x5L0iPG5LG1BccpRGc4sxmcen1GcZchPCF72MypGhSNQ72M0hm1PRO++4AJFv8pOqNkZg1Y8qCgIUc5zNAbCfovWxH+gab6+rzScFkv+/DXpI92iH6jLIsGYH4Su5wYeD33hBy4aOqRB764BNMmmwGvnLmUDneLaA5L83xWpfa9Cf9fDDuGCkcsN+wcH3ugm",
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
