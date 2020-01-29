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
        return $json;
    }

    /**
     * @return string
     */
    private function payloadJsonString()
    {
        return '{
            "country" : "AT",
            "notification_url_1" : "",
            "response_signature" : "",
            "provider_status_code_1" : "0",
            "response_signature_v2" : "",
            "provider_status_code_2" : "0",
            "provider_status_description_2" : "Transaction OK",
            "merchant_crm_id" : "3",
            "completion_time_stamp" : "20200121115207",
            "provider_status_description_1" : "Transaction OK",
            "token_id" : "",
            "payment_method" : "creditcard",
            "cardholder_authentication_status" : "Y",
            "transaction_id" : "",
            "transaction_type" : "purchase",
            "status_code_1" : "201.0000",
            "status_code_2" : "200.1077",
            "status_description_1" : "3d-acquirer:The resource was successfully created.",
            "phone" : "123123123",
            "status_description_2" : "3d-acquirer:Card is eligible for the ACS authentication process.",
            "parent_transaction_id" : "",
            "city" : "city",
            "requested_amount" : "104.4",
            "nonce3d" : "",
            "pareq" : "",
            "merchant_account_id" : "",
            "expiration_month" : "1",
            "street1" : "address 12",
            "email" : "test@test.com",
            "status_severity_2" : "information",
            "status_severity_1" : "information",
            "browser_screen_resolution" : "1920x1080",
            "last_name" : "asdasd",
            "ip_address" : "",
            "card_type" : "visa",
            "browser_ip_address" : "",
            "acs_url" : "",
            "expiration_year" : "2023",
            "masked_account_number" : "",
            "transaction_state" : "success",
            "requested_amount_currency" : "EUR",
            "postal_code" : "1234",
            "request_id" : ""
        }';
    }
}
