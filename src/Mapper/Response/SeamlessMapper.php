<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Mapper\Response;

use Wirecard\PaymentSdk\Constant\ResponseMappingXmlFields;
use Wirecard\PaymentSdk\Constant\SeamlessFields;
use Wirecard\PaymentSdk\Constant\StatusFields;
use Wirecard\PaymentSdk\Helper\XmlBuilder;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\ResponseFactory;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Response\XmlResponseData;

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
     * @var XmlBuilder
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
        $this->paymentXmlBuilder = new XmlBuilder(ResponseMappingXmlFields::PAYMENT);
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

        $paymentXml = $this->paymentXmlBuilder->getXml();

        $xmlResponseData = new XmlResponseData($paymentXml, $this->payload);
        $responseFactory = new ResponseFactory($xmlResponseData);

        return $responseFactory->create();
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
            $amountXmlBuilder = new XmlBuilder(
                ResponseMappingXmlFields::REQUESTED_AMOUNT,
                $this->payload[SeamlessFields::REQUESTED_AMOUNT]
            );
            $amountXmlBuilder->addAttributes(
                [
                    ResponseMappingXmlFields::REQUESTED_AMOUNT_CURRENCY =>
                    $this->payload[SeamlessFields::REQUESTED_AMOUNT_CURRENCY]
                ]
            );

            $this->paymentXmlBuilder->addSimpleXmlObject($amountXmlBuilder->getXml());
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
            $threeDXmlBuilder = new XmlBuilder(ResponseMappingXmlFields::THREE_D);
            $threeDXmlBuilder->addRawObject(
                ResponseMappingXmlFields::ACS_URL,
                $this->payload[SeamlessFields::ACS_URL]
            );
            $threeDXmlBuilder->addRawObject(
                ResponseMappingXmlFields::PAREQ,
                $this->payload[SeamlessFields::PAREQ]
            );
            $threeDXmlBuilder->addRawObject(
                ResponseMappingXmlFields::CARDHOLDER_AUTHENTICATION_STATUS,
                $this->payload[SeamlessFields::CARDHOLDER_AUTHENTICATION_STATUS]
            );

            $this->paymentXmlBuilder->addSimpleXmlObject($threeDXmlBuilder->getXml());
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
            $statusesXmlBuilder = new XmlBuilder(ResponseMappingXmlFields::STATUSES);

            foreach ($statuses as $status) {
                $statusesXmlBuilder->addSimpleXmlObject(
                    (new XmlBuilder(ResponseMappingXmlFields::STATUS))->addAttributes(
                        [
                            StatusFields::CODE => $status[StatusFields::CODE],
                            StatusFields::DESCRIPTION => $status[StatusFields::DESCRIPTION],
                            StatusFields::SEVERITY => $status[StatusFields::SEVERITY],
                        ]
                    )->getXml()
                );
            }
            $statusXml = $statusesXmlBuilder->getXml();
            $this->paymentXmlBuilder->addSimpleXmlObject($statusXml);
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
            $cardXmlBuilder = new XmlBuilder(ResponseMappingXmlFields::CARD_TOKEN);
            $cardXmlBuilder->addRawObject(
                ResponseMappingXmlFields::TOKEN_ID,
                $this->payload[SeamlessFields::TOKEN_ID]
            );
            $cardXmlBuilder->addRawObject(
                ResponseMappingXmlFields::MASKED_ACCOUNT_NUMBER,
                $this->payload[SeamlessFields::MASKED_ACCOUNT_NUMBER]
            );

            $this->paymentXmlBuilder->addSimpleXmlObject($cardXmlBuilder->getXml());
        }
    }

    /**
     * Turns the statuses from the response into an easier to use array format
     *
     * @return array
     * @since 4.0.0
     */
    private function extractStatusesFromResponse()
    {
        //@TODO refactor legacy code of extracting statuses
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
}
