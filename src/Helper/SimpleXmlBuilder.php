<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Helper;

use http\Exception\InvalidArgumentException;

class SimpleXmlBuilder
{
    /**
     * @var \SimpleXMLElement
     */
    private $xml;

    /**
     * XmlBuilder constructor.
     * @param string $namespace
     * @param string $value
     * @since 4.0.0
     */
    public function __construct($namespace, $value = '')
    {
        if (!is_string($namespace)) {
            throw new InvalidArgumentException('The namespace provided is not of type string');
        }

        $this->xml = new \SimpleXMLElement('<' . $namespace .'>' . $value . '</' . $namespace . '>');
    }

    /**
     * @param \SimpleXMLElement $xmlObject
     * @return $this
     * @since 4.0.0
     */
    public function addSimpleXmlObject(\SimpleXMLElement $xmlObject)
    {
        $this->appendAsChild($xmlObject);
        return $this;
    }

    /**
     * @param string $objectName
     * @param mixed $objectValue
     * @param array $attributes
     * @return $this
     * @since 4.0.0
     */
    public function addRawObject($objectName, $objectValue, $attributes = [])
    {
        if (!is_string($objectName)) {
            throw new InvalidArgumentException('The namespace provided is not of type string');
        }

        $newXmlObject = new \SimpleXMLElement(
            '<' . $objectName . '>' . $objectValue .'</' . $objectName . '>'
        );

        foreach ($attributes as $attributeKey => $attributeValue) {
            $newXmlObject->addAttribute($attributeKey, $attributeValue);
        }

        $this->appendAsChild($newXmlObject);
        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function addAttributes($attributes)
    {
        foreach ($attributes as $attributeKey => $attributeValue) {
            $this->xml->addAttribute($attributeKey, $attributeValue);
        }
        return $this;
    }

    /**
     * @return \SimpleXMLElement
     * @since 4.0.0
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * @param \SimpleXMLElement $simpleXmlObject
     * @since 4.0.0
     */
    private function appendAsChild(\SimpleXMLElement $simpleXmlObject)
    {
        $paymentDom = dom_import_simplexml($this->xml);
        $childDom = dom_import_simplexml($simpleXmlObject);
        $paymentDom->appendChild($paymentDom->ownerDocument->importNode($childDom, true));
    }
}
