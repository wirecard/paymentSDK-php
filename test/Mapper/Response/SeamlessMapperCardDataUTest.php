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
use Wirecard\PaymentSdk\Constant\SeamlessFields;
use Wirecard\PaymentSdk\Mapper\Response\SeamlessMapper;
use Wirecard\PaymentSdk\Response\Response;

class SeamlessMapperCardDataUTest extends PHPUnit_Framework_TestCase
{
    const EXPECTED_CARD_TYPE = 'visa';
    const EXPECTED_EXPIRATION_YEAR = '2023';
    const EXPECTED_EXPIRATION_MONTH = '1';

    public function testCardExpirationMonthIsSet()
    {
        $expected = self::EXPECTED_EXPIRATION_MONTH;
        $seamlessMapper = new SeamlessMapper($this->getPayloadWithoutYearAndCardType());
        /** @var Response $result */
        $result = $seamlessMapper->map();
        $this->assertEquals($expected, $result->getCard()->getExpirationMonth());
    }

    public function testCardExpirationYearIsSet()
    {
        $expected = self::EXPECTED_EXPIRATION_YEAR;
        $seamlessMapper = new SeamlessMapper($this->getPayloadWithoutMonthAndCardType());
        /** @var Response $result */
        $result = $seamlessMapper->map();
        $this->assertEquals($expected, $result->getCard()->getExpirationYear());
    }

    public function testCardTypeIsSet()
    {
        $expected = self::EXPECTED_CARD_TYPE;
        $seamlessMapper = new SeamlessMapper($this->getPayloadWithoutMonthAndYear());
        /** @var Response $result */
        $result = $seamlessMapper->map();
        $this->assertEquals($expected, $result->getCard()->getCardType());
    }

    public function testCardExpirationDateIsSet()
    {
        $seamlessMapper = new SeamlessMapper($this->getPayloadWithoutCardType());
        $result = $seamlessMapper->map();

        $this->assertNotEmpty($result->getCard()->getExpirationMonth());
        $this->assertNotEmpty($result->getCard()->getExpirationYear());
    }

    public function testCardExpirationAndTypeIsSet()
    {
        $seamlessMapper = new SeamlessMapper($this->getPayloadArray());
        $result = $seamlessMapper->map();

        $this->assertNotEmpty($result->getCard()->getExpirationMonth());
        $this->assertNotEmpty($result->getCard()->getExpirationYear());
        $this->assertNotEmpty($result->getCard()->getCardType());
    }

    public function testWithoutCardDataIsSet()
    {
        $seamlessMapper = new SeamlessMapper($this->getPayloadWithoutCardData());
        $result = $seamlessMapper->map();

        $this->assertEmpty($result->getCard()->getExpirationMonth());
        $this->assertEmpty($result->getCard()->getExpirationYear());
        $this->assertEmpty($result->getCard()->getCardType());
    }

    /**
     * @return array
     */
    private function getPayloadWithoutMonthAndYear()
    {
        $payload = $this->getPayloadArray();
        unset($payload[SeamlessFields::EXPIRATION_YEAR]);
        unset($payload[SeamlessFields::EXPIRATION_MONTH]);
        return $payload;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutCardType()
    {
        $payload = $this->getPayloadArray();
        unset($payload[SeamlessFields::CARD_TYPE]);
        return $payload;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutYearAndCardType()
    {
        $payload = $this->getPayloadArray();
        unset($payload[SeamlessFields::CARD_TYPE]);
        unset($payload[SeamlessFields::EXPIRATION_YEAR]);
        return $payload;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutMonthAndCardType()
    {
        $payload = $this->getPayloadArray();
        unset($payload[SeamlessFields::EXPIRATION_MONTH]);
        unset($payload[SeamlessFields::CARD_TYPE]);
        return $payload;
    }

    /**
     * @return array
     */
    private function getPayloadWithoutCardData()
    {
        $payload = $this->getPayloadArray();
        unset($payload[SeamlessFields::CARD_TYPE]);
        unset($payload[SeamlessFields::EXPIRATION_YEAR]);
        unset($payload[SeamlessFields::EXPIRATION_MONTH]);
        return $payload;
    }

    /**
     * @return array
     */
    private function getPayloadArray()
    {
        $json = json_decode($this->getPayloadJsonString(), true);
        return $json;
    }

    /**
     * @return string
     */
    private function getPayloadJsonString()
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
            "expiration_month" : "' . self::EXPECTED_EXPIRATION_MONTH . '",
            "street1" : "address 12",
            "email" : "test@test.com",
            "status_severity_2" : "information",
            "status_severity_1" : "information",
            "browser_screen_resolution" : "1920x1080",
            "last_name" : "asdasd",
            "ip_address" : "",
            "card_type" : "' . self::EXPECTED_CARD_TYPE . '",
            "browser_ip_address" : "",
            "acs_url" : "",
            "expiration_year" : "' . self::EXPECTED_EXPIRATION_YEAR . '",
            "masked_account_number" : "",
            "transaction_state" : "success",
            "requested_amount_currency" : "EUR",
            "postal_code" : "1234",
            "request_id" : ""
        }';
    }
}
