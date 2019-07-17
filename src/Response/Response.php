<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Response;

use SimpleXMLElement;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Card;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\PaymentDetails;
use Wirecard\PaymentSdk\Entity\RawCustomField;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Entity\TransactionDetails;
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
     * @var Basket $basket
     */
    protected $basket;

    /**
     * @var Amount $amount
     */
    protected $requestedAmount;

    /**
     * @var AccountHolder
     */
    protected $accountHolder;

    /**
     * @var AccountHolder
     */
    protected $shipping;

    /**
     * @var CustomFieldCollection
     */
    protected $customFields;

    /**
     * @var Card
     */
    protected $card;

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
        $this->setBasket();
        $this->setRequestedAmount();
        $this->setAccountHolder();
        $this->setShipping();
        $this->setCustomFields();
        $this->setCard();
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
                    $arr[$child->getName()] = strval($child);
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
        return $this->customFields;
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

    /**
     * Parse simplexml and create basket object
     *
     * @since 3.0.0
     */
    private function setBasket()
    {
        $basket = new Basket();

        $this->basket = $basket->parseFromXml($this->simpleXml);
    }

    /**
     * Parse simplexml and create requestedAmount object
     *
     * @since 3.0.0
     */
    private function setRequestedAmount()
    {
        if ($this->simpleXml->{'requested-amount'}->count() < 1) {
            return;
        }

        $this->requestedAmount = new Amount(
            (float)$this->simpleXml->{'requested-amount'},
            (string)$this->simpleXml->{'requested-amount'}->attributes()->currency
        );
    }

    /**
     * @since 3.0.0
     */
    private function setAccountHolder()
    {
        $accountHolderXml = $this->simpleXml->{'account-holder'};
        if (!isset($accountHolderXml)) {
            return;
        }

        $this->accountHolder = new AccountHolder($accountHolderXml);
    }

    /**
     * @since 3.0.0
     */
    private function setShipping()
    {
        $shipping = $this->simpleXml->shipping;
        if (!isset($shipping)) {
            return;
        }
        $this->shipping = new AccountHolder($shipping);
    }

    /**
     * parse simplexml to load all custom fields
     *
     * @since 3.0.0
     */
    private function setCustomFields()
    {
        $customFieldCollection = new CustomFieldCollection();

        if (isset($this->simpleXml->{'custom-fields'})) {
            /** @var SimpleXMLElement $field */
            foreach ($this->simpleXml->{'custom-fields'}->children() as $field) {
                $customField = $this->convertToCustomField($field);
                if (!is_null($customField)) {
                    $customFieldCollection->add($customField);
                }
            }
        }
        $this->customFields = $customFieldCollection;
    }

    /**
     * Convert the xml field into a CustomField object
     *
     * Return null if field name is empty or xml object does not provides field name/field value as attribute
     *
     * @param SimpleXMLElement $field
     * @return null|CustomField
     */
    private function convertToCustomField($field)
    {
        if (!empty($field->attributes())) {
            if (isset($field->attributes()->{'field-name'}) && isset($field->attributes()->{'field-value'})) {
                $rawName = (string)$field->attributes()->{'field-name'};
                list($normalizedName, $prefix) = $this->splitFieldNameAndPrefix($rawName);
                $value = (string)$field->attributes()->{'field-value'};
                if (!empty($normalizedName)) {
                    return new CustomField($normalizedName, $value, $prefix);
                }
            }
        }
        return null;
    }

    /**
     * Auto detect the PHPSDK prefix for customfields
     *
     * @param string $rawName
     * @return array
     */
    private function splitFieldNameAndPrefix($rawName)
    {
        $normalizedName = '';
        $prefix = '';

        if (!empty($rawName)) {
            if (strpos($rawName, CustomField::DEFAULT_PREFIX) === 0) {
                $normalizedName = substr($rawName, strlen(CustomField::DEFAULT_PREFIX));
                $prefix = CustomField::DEFAULT_PREFIX;
            } else {
                $normalizedName = $rawName;
            }
        }

        return [$normalizedName, $prefix];
    }

    /**
     * @return Basket
     * @since 3.0.0
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * @return AccountHolder
     * @since 3.0.0
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @return AccountHolder
     * @since 3.0.0
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @return Amount
     * @since 3.0.0
     */
    public function getRequestedAmount()
    {
        return $this->requestedAmount;
    }

    public function getPaymentDetails()
    {
        return new PaymentDetails($this->simpleXml);
    }

    public function getTransactionDetails()
    {
        return new TransactionDetails($this->simpleXml);
    }

    public function getCard()
    {
        return $this->card;
    }

    public function setCard()
    {
        $this->card = new Card($this->simpleXml);
    }
}
