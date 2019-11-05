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
     * SeamlessMapper constructor.
     * @param array $payload
     * @since 4.0.0
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function map()
    {
        $simpleXml = new SimpleXMLElement('<payment></payment>');

        $this->mapCommonSeamlessFields($simpleXml);
        $this->addCardToken($simpleXml);

        if (array_key_exists(SeamlessFields::ACS_URL, $this->payload)) {
            return $this->makeFormInteractionResponse($simpleXml);
        }

        if ($this->payload['transaction_state'] === 'success') {
            return new SuccessResponse($simpleXml);
        }

        return new FailureResponse($simpleXml);
    }

    /**
     * Maps all pre-existing fields the seamless sends.
     *
     * @param SimpleXMLElement $simpleXml
     * @since 4.0.0
     */
    private function mapCommonSeamlessFields(SimpleXMLElement $simpleXml)
    {
        $simpleXml->addChild(
            ResponseMappingXmlFields::MERCHANT_ACCOUNT_ID,
            $this->payload[SeamlessFields::MERCHANT_ACCOUNT_ID]
        );

        $simpleXml->addChild(
            ResponseMappingXmlFields::TRANSACTION_ID,
            $this->payload[SeamlessFields::TRANSACTION_ID]
        );

        $simpleXml->addChild(
            ResponseMappingXmlFields::TRANSACTION_STATE,
            $this->payload[SeamlessFields::TRANSACTION_STATE]
        );

        $simpleXml->addChild(
            ResponseMappingXmlFields::TRANSACTION_TYPE,
            $this->payload[SeamlessFields::TRANSACTION_TYPE]
        );

        $simpleXml->addChild(
            ResponseMappingXmlFields::PAYMENT_METHOD,
            $this->payload[SeamlessFields::PAYMENT_METHOD]
        );

        $simpleXml->addChild(
            ResponseMappingXmlFields::REQUEST_ID,
            $this->payload[SeamlessFields::REQUEST_ID]
        );


        $this->addRequestedAmount($simpleXml);
        $this->addThreeDInformation($simpleXml);
        $this->addParentTransactionId($simpleXml);
        $this->addStatuses($simpleXml);
    }

    /**
     * Add the requested amount to our XML
     *
     * @param SimpleXMLElement $simpleXml
     * @since 4.0.0
     */
    private function addRequestedAmount(SimpleXMLElement $simpleXml)
    {
        if (array_key_exists(SeamlessFields::REQUESTED_AMOUNT, $this->payload) &&
            array_key_exists(SeamlessFields::REQUESTED_AMOUNT_CURRENCY, $this->payload)
        ) {
            $amountSimpleXml = new SimpleXMLElement(
                '<requested-amount>'.$this->payload[SeamlessFields::REQUESTED_AMOUNT].'</requested-amount>'
            );
            $amountSimpleXml->addAttribute(
                ResponseMappingXmlFields::REQUESTED_AMOUNT_CURRENCY,
                $this->payload[SeamlessFields::REQUESTED_AMOUNT_CURRENCY]
            );
            //@TODO fix this shit with return and appending a node
            $this->simpleXmlAppendNode($simpleXml, $amountSimpleXml);
        }
    }

    /**
     * Add 3D information to our XML
     *
     * @param SimpleXMLElement $simpleXml
     * @since 4.0.0
     */
    private function addThreeDInformation(SimpleXMLElement $simpleXml)
    {
        if (array_key_exists(SeamlessFields::ACS_URL, $this->payload) &&
            array_key_exists(SeamlessFields::PAREQ, $this->payload) &&
            array_key_exists(SeamlessFields::CARDHOLDER_AUTHENTICATION_STATUS, $this->payload)
        ) {
            $threeD = new SimpleXMLElement('<three-d></three-d>');
            $threeD->addChild(ResponseMappingXmlFields::ACS_URL, $this->payload[SeamlessFields::ACS_URL]);
            $threeD->addChild(ResponseMappingXmlFields::PAREQ, $this->payload[SeamlessFields::PAREQ]);
            $threeD->addChild(
                ResponseMappingXmlFields::CARDHOLDER_AUTHENTICATION_STATUS,
                $this->payload[SeamlessFields::CARDHOLDER_AUTHENTICATION_STATUS]
            );

            //@TODO fix this shit with return and appending node
            $this->simpleXmlAppendNode($simpleXml, $threeD);
        }
    }

    /**
     * Add the parent transaction id to our XML
     *
     * @param SimpleXMLElement $simpleXml
     * @since 4.0.0
     */
    private function addParentTransactionId(SimpleXMLElement $simpleXml)
    {
        if (array_key_exists(SeamlessFields::PARENT_TRANSACTION_ID, $this->payload)) {
            $simpleXml->addChild(
                ResponseMappingXmlFields::PARENT_TRANSACTION_ID,
                $this->payload[SeamlessFields::PARENT_TRANSACTION_ID]
            );
        }
    }

    /**
     * Add all the status information to our XML.
     *
     * @param SimpleXMLElement $simpleXml
     * @since 4.0.0
     */
    private function addStatuses(SimpleXMLElement $simpleXml)
    {
        $statuses = $this->extractStatusesFromResponse();
        if (count($statuses) > 0) {
            $statusesXml = new SimpleXMLElement('<statuses></statuses>');

            foreach ($statuses as $status) {
                $statusXml = $this->makeStatus($status);
                $this->simpleXmlAppendNode($statusesXml, $statusXml);
            }
            //@TODO add shit simple xml shit
            $this->simpleXmlAppendNode($simpleXml, $statusesXml);
        }
    }

    /**
     * Add the credit card token to our XML.
     *
     * @param SimpleXMLElement $simpleXml
     * @since 4.0.0
     */
    private function addCardToken(SimpleXMLElement $simpleXml)
    {
        if (array_key_exists(SeamlessFields::TOKEN_ID, $this->payload) &&
            array_key_exists(SeamlessFields::MASKED_ACCOUNT_NUMBER, $this->payload)
        ) {
            $card_token = new SimpleXMLElement('<card-token></card-token>');
            $card_token->addChild(
                ResponseMappingXmlFields::TOKEN_ID,
                $this->payload[SeamlessFields::TOKEN_ID]
            );
            $card_token->addChild(
                ResponseMappingXmlFields::MASKED_ACCOUNT_NUMBER,
                $this->payload[SeamlessFields::MASKED_ACCOUNT_NUMBER]
            );
            //@TODO fix this shit
            $this->simpleXmlAppendNode($simpleXml, $card_token);
        }
    }

    /**
     * Build a FormInteractionResponse and add the form fields for a successful redirect
     *
     * @param SimpleXMLElement $simpleXml
     * @return FormInteractionResponse
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
        $status = new SimpleXMLElement('<status></status>');
        $status->addAttribute(StatusFields::CODE, $statusData[StatusFields::CODE]);
        $status->addAttribute(StatusFields::DESCRIPTION, $statusData[StatusFields::DESCRIPTION]);
        $status->addAttribute(StatusFields::SEVERITY, $statusData[StatusFields::SEVERITY]);

        return $status;
    }

    /**
     * @param SimpleXMLElement $appendTo
     * @param SimpleXMLElement $from
     *
     * @since 4.0.0
     */
    private function simpleXmlAppendNode($appendTo, $from)
    {
        $toDom = dom_import_simplexml($appendTo);
        $fromDom = dom_import_simplexml($from);
        $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
    }
}
