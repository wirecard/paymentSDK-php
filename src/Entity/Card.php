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
