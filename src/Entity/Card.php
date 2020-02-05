<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use SimpleXMLElement;

class Card implements MappableEntity
{
    private $expirationMonth;
    private $expirationYear;
    private $type;
    private $merchantTokenizationFlag;
    private $maskedPan;
    private $token;

    /**
     * if simpleXml is set parse the data form xml
     * @param SimpleXMLElement $simpleXml
     * @since 3.2.0
     */
    public function __construct($simpleXml = null)
    {
        if ($simpleXml) {
            $this->parseFromXml($simpleXml);
        }
    }

    /**
     * @param mixed $expirationMonth
     */
    public function setExpirationMonth($expirationMonth)
    {
        $this->expirationMonth = $expirationMonth;
    }

    /**
     * @param mixed $expirationYear
     */
    public function setExpirationYear($expirationYear)
    {
        $this->expirationYear = $expirationYear;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $card = [];

        if (isset($this->expirationMonth)) {
            $card['expiration-month'] = $this->expirationMonth;
        }

        if (isset($this->expirationYear)) {
            $card['expiration-year'] = $this->expirationYear;
        }

        if (isset($this->type)) {
            $card['card-type'] = $this->type;
        }

        if (isset($this->merchantTokenizationFlag)) {
            $card['merchant-tokenization-flag'] = boolval($this->merchantTokenizationFlag);
        }

        return $card;
    }

    public function setMerchantTokenizationFlag($merchantTokenizationFlag)
    {
        $this->merchantTokenizationFlag = $merchantTokenizationFlag;
    }

    /**
     * Parse card from response xml
     * @param SimpleXMLElement $simpleXml
     * @since 3.2.0
     */
    private function parseFromXml($simpleXml)
    {
        if (isset($simpleXml->{'card-token'}->{'masked-account-number'})) {
            $this->maskedPan = $simpleXml->{'card-token'}->{'masked-account-number'};
        }
        if (isset($simpleXml->{'card-token'}->{'token-id'})) {
            $this->token = $simpleXml->{'card-token'}->{'token-id'};
        }
    }

    /**
     * Get html table with the set data
     * @param array $options
     * @return string
     * @since 3.2.0
     */
    public function getAsHtml($options = [])
    {
        $defaults = [
            'table_id' => null,
            'table_class' => null,
            'translations' => [
                'title' => 'Card',
                'maskedPan' => 'Masked Pan',
                'token' => 'Token'
            ],
        ];

        $options = array_merge($defaults, $options);
        $translations = $options['translations'];

        $html = "<table id='{$options['table_id']}' class='{$options['table_class']}'>";
        $html .= "<tbody><tr><td>" . $translations['maskedPan'] . "</td><td>" . $this->maskedPan . "</td></tr>";
        $html .= "<tr><td>" . $translations['token'] . "</td><td>" . $this->token . "</td></tr></tbody>";
        $html .= "</table>";

        return $html;
    }
}
