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

/**
 * Class TransactionDetails
 * @package Wirecard\PaymentSdk\Entity
 *
 * An entity representing transaction details
 * @since 3.2.0
 */
class TransactionDetails
{
    /**
     * @var string
     */
    private $maid;

    /**
     * @var string
     */
    private $transactionID;

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var string
     */
    private $transactionType;

    /**
     * @var string
     */
    private $transactionState;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $requestedAmount;

    /**
     * @var string
     */
    private $descriptor;

    /**
     * TransactionDetails constructor.
     * @param SimpleXMLElement $simpleXml
     * @since 3.2.0
     */
    public function __construct($simpleXml)
    {
        if (isset($simpleXml->{'merchant-account-id'})) {
            $this->maid = $simpleXml->{'merchant-account-id'};
        }
        if (isset($simpleXml->{'transaction-id'})) {
            $this->transactionID = $simpleXml->{'transaction-id'};
        }
        if (isset($simpleXml->{'request-id'})) {
            $this->requestId = $simpleXml->{'request-id'};
        }
        if (isset($simpleXml->{'transaction-type'})) {
            $this->transactionType = $simpleXml->{'transaction-type'};
        }
        if (isset($simpleXml->{'transaction-state'})) {
            $this->transactionState = $simpleXml->{'transaction-state'};
        }
        if (isset($simpleXml->{'currency'})) {
            $this->currency = $simpleXml->{'currency'};
        }
        if (isset($simpleXml->{'requested-amount'})) {
            $this->requestedAmount = $simpleXml->{'requested-amount'};
        }
        if (isset($simpleXml->{'descriptor'})) {
            $this->descriptor = $simpleXml->{'descriptor'};
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
                'title' => 'Transaction Details'
            ],
        ];

        $options = array_merge($defaults, $options);
        $translations = $options['translations'];

        $html = "<table id='{$options['table_id']}' class='{$options['table_class']}'><tbody>";
        foreach ($this->getAllSetData() as $key => $value) {
            $html .= "<tr><td>" . $this->translate($key, $translations) . "</td><td>" . $value . "</td></tr>";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Return all set data
     * @return array
     * @since 3.2.0
     */
    private function getAllSetData()
    {
        $data = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($value) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Translate the table keys
     * @param $key
     * @param $translations
     * @return mixed
     * @since 3.2.0
     */
    private function translate($key, $translations)
    {
        if ($translations != null && isset($translations[$key])) {
            return $translations[$key];
        }

        return $key;
    }
}
