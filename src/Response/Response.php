<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Response;

use SimpleXMLElement;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class Response
 * @package Wirecard\PaymentSdk\Response
 */
abstract class Response
{
    /**
     * @var StatusCollection
     */
    private $statusCollection;

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var boolean
     */
    private $validSignature = true;

    /**
     * @var SimpleXMLElement
     */
    protected $simpleXml;

    /**
     * @var string
     */
    protected $transactionType;

    /**
     * @var string
     */
    protected $operation = null;

    /**
     * Response constructor.
     * @param SimpleXMLElement $simpleXml
     * @throws MalformedResponseException
     */
    public function __construct($simpleXml)
    {
        $this->simpleXml = $simpleXml;
        $this->statusCollection = $this->generateStatusCollection();
        $this->setValueForRequestId();
    }

    /**
     * get the raw response data of the called interface
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->simpleXml->asXML();
    }

    /**
     * get the response in a flat array
     *
     * @return array
     */
    public function getData()
    {
        $dataArray = self::xmlToArray($this->simpleXml);
        return self::arrayFlatten($dataArray);
    }

    /**
     * @return bool
     */
    public function isValidSignature()
    {
        return $this->validSignature;
    }

    /**
     * @return StatusCollection
     */
    public function getStatusCollection()
    {
        return $this->statusCollection;
    }

    /**
     * @param bool $validSignature
     */
    public function setValidSignature($validSignature)
    {
        $this->validSignature = $validSignature;
    }

    /**
     * @param string $element
     * @return string
     * @throws MalformedResponseException
     */
    public function findElement($element)
    {
        if (isset($this->simpleXml->{$element})) {
            return (string)$this->simpleXml->{$element};
        }

        throw new MalformedResponseException('Missing ' . $element . ' in response.');
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * get the collection of status returned by Wirecard's Payment Processing Gateway
     * @return StatusCollection
     * @throws MalformedResponseException
     */
    private function generateStatusCollection()
    {
        $collection = new StatusCollection();

        /**
         * @var $statuses \SimpleXMLElement
         */
        if (!isset($this->simpleXml->{'statuses'})) {
            throw new MalformedResponseException('Missing statuses in response.');
        }
        $statuses = $this->simpleXml->{'statuses'};
        if (count($statuses->{'status'}) > 0) {
            foreach ($statuses->{'status'} as $statusNode) {
                /**
                 * @var $statusNode \SimpleXMLElement
                 */
                $attributes = $statusNode->attributes();

                if ((string)$attributes['code'] !== '') {
                    $code = (string)$attributes['code'];
                } else {
                    throw new MalformedResponseException('Missing status code in response.');
                }
                if ((string)$attributes['description'] !== '') {
                    $description = (string)$attributes['description'];
                } else {
                    throw new MalformedResponseException('Missing status description in response.');
                }
                if ((string)$attributes['severity'] !== '') {
                    $severity = (string)$attributes['severity'];
                } else {
                    throw new MalformedResponseException('Missing status severity in response.');
                }
                $status = new Status($code, $description, $severity);
                $collection->add($status);
            }
        }

        return $collection;
    }

    /**
     * @param SimpleXMLElement $simplexml
     * @return array
     */
    private static function xmlToArray($simplexml)
    {
        $arr = array();

        /**
         * @var SimpleXMLElement $child
         */
        foreach ($simplexml->children() as $child) {
            if ($child->children()->count() == 0 && $child->attributes()->count() == 0) {
                $arr[$child->getName()] = strval($child);
            } else {
                if ($child->children()->count() == 0 && $child->attributes()->count() > 0) {
                    foreach ($child->attributes() as $attrs) {
                        /** @var SimpleXMLElement $attrs */
                        $arr[$attrs->getName()] = strval($attrs);
                    }
                } else {
                    $arr[$child->getName()][] = self::xmlToArray($child);
                }
            }
        }
        return $arr;
    }

    /**
     * convert a multidimensional array into a simple one-dimensional array
     *
     * @param array $array
     * @return array
     */
    private static function arrayFlatten($array, $prefix = '')
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + self::arrayFlatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = trim(preg_replace('/\s+/', ' ', $value));
            }
        }
        return $result;
    }

    /**
     * Get the transaction type of the response
     *
     * The transaction type is set in the request and should therefore be identical in the response.
     * @return mixed
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    protected function setValueForRequestId()
    {
        $this->requestId = $this->findElement('request-id');
    }

    /**
     * @return CustomFieldCollection
     */
    public function getCustomFields()
    {
        $customFieldCollection = new CustomFieldCollection();

        if (isset($this->simpleXml->{'custom-fields'})) {
            /** @var SimpleXMLElement $field */
            foreach ($this->simpleXml->{'custom-fields'}->children() as $field) {
                if (isset($field->attributes()->{'field-name'}) && isset($field->attributes()->{'field-value'})) {
                    $name = substr((string)$field->attributes()->{'field-name'}, 7);
                    $value = (string)$field->attributes()->{'field-value'};
                    $customFieldCollection->add(new CustomField($name, $value));
                }
            }
        }
        return $customFieldCollection;
    }

    /**
     * Set the operation executed
     *
     * Necessary mainly for cancel, so that it is possible to see whether
     * there was just a void or a refund.
     * @param string $operation
     * @since 0.6.5
     */
    public function setOperation($operation = null)
    {
        $this->operation = $operation;
    }

    /**
     * @return string|null
     * @since 0.6.5
     */
    public function getOperation()
    {
        return $this->operation;
    }
}
