<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Response;

use Wirecard\PaymentSdk\Constant\FormFields;
use Wirecard\PaymentSdk\Constant\SeamlessFields;
use Wirecard\PaymentSdk\Entity\FormFieldMap;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class XmlResponseData
 * @package Wirecard\PaymentSdk\Response
 * @since 4.0.0
 */
class XmlResponseData implements ResponseDataInterface
{
    const XML_DATA = 'xml';

    /**
     * @var \SimpleXMLElement
     */
    private $xmlData;

    /**
     * @var FormFieldMap
     */
    private $formFields;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $payload;

    /**
     * XmlResponseData constructor.
     * @param \SimpleXMLElement $xmlData
     * @param array $payload
     * @since 4.0.0
     */
    public function __construct(\SimpleXMLElement $xmlData, array $payload)
    {
        $this->xmlData = $xmlData;
        $this->payload = $payload;

        if ($this->getResponseType() === self::FORM_INTERACTION) {
            $this->formFields = $this->makeFormFields();
            $this->url = $this->payload[SeamlessFields::ACS_URL];
        }
    }

    /**
     * @return string
     * @since 4.0.0
     */
    public function getResponseType()
    {
        if (array_key_exists(SeamlessFields::ACS_URL, $this->payload)) {
            return self::FORM_INTERACTION;
        }

        if ($this->payload[SeamlessFields::TRANSACTION_STATE] === self::SUCCESS) {
            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    /**
     * @return string
     * @since 4.0.0
     */
    public function getDataType()
    {
        return self::XML_DATA;
    }

    /**
     * @return \SimpleXMLElement
     * @since 4.0.0
     */
    public function getData()
    {
        return $this->xmlData;
    }

    /**
     * @return FormFieldMap
     * @since 4.0.0
     */
    public function getFormFields()
    {
        return $this->formFields;
    }

    /**
     * @return string
     * @since 4.0.0
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Build the form fields required for a 3DS FormInteractionResponse
     *
     * @return FormFieldMap
     * @throws MalformedResponseException
     * @since 4.0.0
     */
    private function makeFormFields()
    {
        if (!array_key_exists(SeamlessFields::PROCESSING_URL, $this->payload)) {
            throw new MalformedResponseException('Missing notification_url_1 in response');
        }

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
}
