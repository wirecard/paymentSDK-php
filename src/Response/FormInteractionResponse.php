<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Response;

use Wirecard\PaymentSdk\Entity\FormFieldMap;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class FormInteractionResponse
 * @package Wirecard\PaymentSdk\Response
 *
 */
class FormInteractionResponse extends Response
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var FormFieldMap
     */
    private $formFields;

    /**
     * FormInteractionResponse constructor.
     * @param \SimpleXMLElement $simpleXml
     * @param string $url
     * @throws MalformedResponseException
     */
    public function __construct($simpleXml, $url)
    {
        parent::__construct($simpleXml);
        $this->url = $url;
        $this->transactionId = $this->findElement('transaction-id');
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return FormFieldMap
     */
    public function getFormFields()
    {
        return $this->formFields;
    }

    /**
     * @param FormFieldMap $formFields
     */
    public function setFormFields($formFields)
    {
        $this->formFields = $formFields;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
