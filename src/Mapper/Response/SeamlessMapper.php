<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Mapper\Response;

use SimpleXMLElement;
use Wirecard\PaymentSdk\Constant\FormFields;
use Wirecard\PaymentSdk\Constant\ResponseMappingXmlFields;
use Wirecard\PaymentSdk\Constant\SeamlessFields;
use Wirecard\PaymentSdk\Constant\StatusFields;
use Wirecard\PaymentSdk\Entity\FormFieldMap;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Helper\SimpleXmlBuilder;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Class SeamlessMapper
 * @package Wirecard\PaymentSdk\Mapper\Response
 * @since 4.0.0
 */
class SeamlessMapper implements MapperInterface
{
    /**
     * @var array
     */
    private $payload;

    /**
     * @var SimpleXmlBuilder
     */
    private $paymentXmlBuilder;

    /**
     * SeamlessMapper constructor.
     * @param array $payload
     * @since 4.0.0
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
        $this->paymentXmlBuilder = new SimpleXmlBuilder(ResponseMappingXmlFields::PAYMENT);
    }

    /**
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     * @since 4.0.0 Refactoring of the ResponseMapper, everything for nvp mapping is
     * taken out of the ResponseMapper and grouped here into methods.
     */
    public function map()
    {
        //@TODO Check which fields are mandatory and which are skipped or mapped only when they are send.
        $this->mapMandatoryFields();
        $this->mapOptionalFields();

        $paymentSimpleXml = $this->paymentXmlBuilder->getXml();

        //@TODO implementation of a response factory to create the appropriate response type.
        if (array_key_exists(SeamlessFields::ACS_URL, $this->payload)) {
            return $this->makeFormInteractionResponse($paymentSimpleXml);
        }

        if ($this->payload['transaction_state'] === 'success') {
            return new SuccessResponse($paymentSimpleXml);
        }

        return new FailureResponse($paymentSimpleXml);
    }

    /**
     * map all mandatory fields
     * @since 4.0.0
     */
    private function mapMandatoryFields()
    {
        $this->paymentXmlBuilder->addRawObject(
            ResponseMappingXmlFields::MERCHANT_ACCOUNT_ID,
            $this->payload[SeamlessFields::MERCHANT_ACCOUNT_ID]
        );

        $this->paymentXmlBuilder->addRawObject(
            ResponseMappingXmlFields::MERCHANT_ACCOUNT_ID,
            $this->payload[SeamlessFields::MERCHANT_ACCOUNT_ID]
        );

        $this->paymentXmlBuilder->addRawObject(
            ResponseMappingXmlFields::TRANSACTION_ID,
            $this->payload[SeamlessFields::TRANSACTION_ID]
        );

        $this->paymentXmlBuilder->addRawObject(
            ResponseMappingXmlFields::TRANSACTION_STATE,
            $this->payload[SeamlessFields::TRANSACTION_STATE]
        );

        $this->paymentXmlBuilder->addRawObject(
            ResponseMappingXmlFields::TRANSACTION_TYPE,
            $this->payload[SeamlessFields::TRANSACTION_TYPE]
        );

        $this->paymentXmlBuilder->addRawObject(
            ResponseMappingXmlFields::PAYMENT_METHOD,
            $this->payload[SeamlessFields::PAYMENT_METHOD]
        );

        $this->paymentXmlBuilder->addRawObject(
            ResponseMappingXmlFields::REQUEST_ID,
            $this->payload[SeamlessFields::REQUEST_ID]
        );
    }

    /**
     * map all optional fields
     * @since 4.0.0
     */
    private function mapOptionalFields()
    {
        $this->addRequestedAmount();
        $this->addThreeDInformation();
        $this->addParentTransactionId();
        $this->addStatuses();
        $this->addCard();
    }

    /**
     * Add the requested amount to our XML
     * @since 4.0.0
     */
    private function addRequestedAmount()
    {
        if (array_key_exists(SeamlessFields::REQUESTED_AMOUNT, $this->payload) &&
            array_key_exists(SeamlessFields::REQUESTED_AMOUNT_CURRENCY, $this->payload)
        ) {
            $amountSimpleXml = (new SimpleXmlBuilder(
                ResponseMappingXmlFields::REQUESTED_AMOUNT,
                $this->payload[SeamlessFields::REQUESTED_AMOUNT]
            ))->addAttributes(
                [
                    ResponseMappingXmlFields::REQUESTED_AMOUNT_CURRENCY =>
                    $this->payload[SeamlessFields::REQUESTED_AMOUNT_CURRENCY]
                ]
            )->getXml();

            $this->paymentXmlBuilder->addSimpleXmlObject($amountSimpleXml);
        }
    }

    /**
     * Add 3D information to our XML
     * @since 4.0.0
     */
    private function addThreeDInformation()
    {
        if (array_key_exists(SeamlessFields::ACS_URL, $this->payload) &&
            array_key_exists(SeamlessFields::PAREQ, $this->payload) &&
            array_key_exists(SeamlessFields::CARDHOLDER_AUTHENTICATION_STATUS, $this->payload)
        ) {
            $threeDSimpleXmlBuilder = new SimpleXmlBuilder(ResponseMappingXmlFields::THREE_D);
            $threeDSimpleXml = $threeDSimpleXmlBuilder->addRawObject(
                ResponseMappingXmlFields::ACS_URL,
                $this->payload[SeamlessFields::ACS_URL]
            )->addRawObject(
                ResponseMappingXmlFields::PAREQ,
                $this->payload[SeamlessFields::PAREQ]
            )->addRawObject(
                ResponseMappingXmlFields::CARDHOLDER_AUTHENTICATION_STATUS,
                $this->payload[SeamlessFields::CARDHOLDER_AUTHENTICATION_STATUS]
            )->getXml();

            $this->paymentXmlBuilder->addSimpleXmlObject($threeDSimpleXml);
        }
    }

    /**
     * Add the parent transaction id to our XML
     * @since 4.0.0
     */
    private function addParentTransactionId()
    {
        if (array_key_exists(SeamlessFields::PARENT_TRANSACTION_ID, $this->payload)) {
            $this->paymentXmlBuilder->addRawObject(
                ResponseMappingXmlFields::PARENT_TRANSACTION_ID,
                $this->payload[SeamlessFields::PARENT_TRANSACTION_ID]
            );
        }
    }

    /**
     * Add all the status information to our XML.
     * @since 4.0.0
     */
    private function addStatuses()
    {
        $statuses = $this->extractStatusesFromResponse();
        if (count($statuses) > 0) {
            $statusesSimpleXmlBuilder = new SimpleXmlBuilder(ResponseMappingXmlFields::STATUSES);

            foreach ($statuses as $status) {
                $statusesSimpleXmlBuilder->addSimpleXmlObject($this->makeStatus($status));
            }
            $statusSimpleXml = $statusesSimpleXmlBuilder->getXml();
            $this->paymentXmlBuilder->addSimpleXmlObject($statusSimpleXml);
        }
    }

    /**
     * Add the credit card token to our XML.
     * @since 4.0.0
     */
    private function addCard()
    {
        if (array_key_exists(SeamlessFields::TOKEN_ID, $this->payload) &&
            array_key_exists(SeamlessFields::MASKED_ACCOUNT_NUMBER, $this->payload)
        ) {
            $cardSimpleXmlBuilder = new SimpleXmlBuilder(ResponseMappingXmlFields::CARD_TOKEN);
            $cardSimpleXml = $cardSimpleXmlBuilder->addRawObject(
                ResponseMappingXmlFields::TOKEN_ID,
                $this->payload[SeamlessFields::TOKEN_ID]
            )->addRawObject(
                ResponseMappingXmlFields::MASKED_ACCOUNT_NUMBER,
                $this->payload[SeamlessFields::MASKED_ACCOUNT_NUMBER]
            )->getXml();

            $this->paymentXmlBuilder->addSimpleXmlObject($cardSimpleXml);
        }
    }

    /**
     * Build a FormInteractionResponse and add the form fields for a successful redirect
     *
     * @param SimpleXMLElement $simpleXml
     * @return FormInteractionResponse
     * @since 4.0.0
     */
    private function makeFormInteractionResponse(SimpleXMLElement $simpleXml)
    {
        if (!array_key_exists(SeamlessFields::PROCESSING_URL, $this->payload)) {
            throw new MalformedResponseException('Missing notification_url_1 in response');
        }

        $fields = $this->makeFormFields();
        $response = new FormInteractionResponse($simpleXml, $this->payload[SeamlessFields::ACS_URL]);
        $response->setFormFields($fields);

        return $response;
    }

    /**
     * Build the form fields required for a 3DS FormInteractionResponse
     *
     * @return FormFieldMap;
     * @since 4.0.0
     */
    private function makeFormFields()
    {
        $fields = new FormFieldMap();
        $fields->add(FormFields::FORM_FIELD_TERM_URL, (string)$this->payload[SeamlessFields::PROCESSING_URL]);
        $fields->add(FormFields::FORM_FIELD_PAREQ, (string)$this->payload[SeamlessFields::PAREQ]);
        $fields->add(
            FormFields::FORM_FIELD_MD,
            http_build_query([
                SeamlessFields::MERCHANT_ACCOUNT_ID => $this->payload[SeamlessFields::MERCHANT_ACCOUNT_ID],
                SeamlessFields::TRANSACTION_TYPE => $this->payload[SeamlessFields::TRANSACTION_TYPE],
                SeamlessFields::TRANSACTION_ID => $this->payload[SeamlessFields::TRANSACTION_ID],
                SeamlessFields::NONCE3D => $this->payload[SeamlessFields::NONCE3D],
            ])
        );

        return $fields;
    }

    /**
     * Turns the statuses from the response into an easier to use array format
     *
     * @return array
     * @since 4.0.0
     */
    private function extractStatusesFromResponse()
    {
        $statuses = [];

        foreach ($this->payload as $key => $value) {
            if (strpos($key, StatusFields::PATTERN) === 0) {
                if (strpos($key, StatusFields::CODE_PATTERN) === 0) {
                    $number = str_replace(StatusFields::CODE_PATTERN, '', $key);
                    $statuses[$number][StatusFields::CODE] = $value;
                }
                if (strpos($key, StatusFields::SEVERITY_PATTERN) === 0) {
                    $number = str_replace(StatusFields::SEVERITY_PATTERN, '', $key);
                    $statuses[$number][StatusFields::SEVERITY] = $value;
                }
                if (strpos($key, StatusFields::DESCRIPTION_PATTERN) === 0) {
                    $number = str_replace(StatusFields::DESCRIPTION_PATTERN, '', $key);
                    $statuses[$number][StatusFields::DESCRIPTION] = $value;
                }
            }
        }

        return $statuses;
    }

    /**
     * Maps status data to a well-formed XML element
     *
     * @param $statusData
     * @return SimpleXMLElement
     * @since 4.0.0
     */
    private function makeStatus($statusData)
    {
        $statusXmlBuilder = new SimpleXmlBuilder(ResponseMappingXmlFields::STATUS);
        return $statusXmlBuilder->addAttributes(
            [
                StatusFields::CODE => $statusData[StatusFields::CODE],
                StatusFields::DESCRIPTION => $statusData[StatusFields::DESCRIPTION],
                StatusFields::SEVERITY => $statusData[StatusFields::SEVERITY],
            ]
        )->getXml();
    }
}
