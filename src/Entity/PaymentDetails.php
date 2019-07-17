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
 * Class PaymentDetails
 * @package Wirecard\PaymentSdk\Entity
 *
 * An entity representing payment details
 * @since 3.2.0
 */
class PaymentDetails
{
    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $timeStamp;

    /**
     * @var string
     */
    private $customerId;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * TransactionDetails constructor.
     * @param SimpleXMLElement $simpleXml
     * @since 3.2.0
     */
    public function __construct($simpleXml)
    {
        if (isset($simpleXml->{'payment-methods'}[0]->{'payment-method'}['name'])) {
            $this->paymentMethod = $simpleXml->{'payment-methods'}[0]->{'payment-method'}['name'];
        }
        if (isset($simpleXml->{'completion-time-stamp'})) {
            $this->timeStamp = $simpleXml->{'completion-time-stamp'};
        }
        if (isset($simpleXml->{'consumer-id'})) {
            $this->customerId = $simpleXml->{'consumer-id'};
        }
        if (isset($simpleXml->{'ip-address'})) {
            $this->ip = $simpleXml->{'ip-address'};
        }
        if (isset($simpleXml->{'order-number'})) {
            $this->orderNumber = $simpleXml->{'order-number'};
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
            'paymentMethod' => null
        ];

        $options = array_merge($defaults, $options);
        $translations = $options['translations'];

        $html = "<table id='{$options['table_id']}' class='{$options['table_class']}'><tbody>";
        foreach ($this->getAllSetData() as $key => $value) {
            if ($key == 'paymentMethod' && $options['paymentMethod'] !== null) {
                $html .= "<tr><td>" . $this->translate($key, $translations) . '</td><td><img src="' .
                    $options['paymentMethod'] . $value . '.png" /></td></tr>';
            } else {
                $html .= "<tr><td>" . $this->translate($key, $translations) . "</td><td>" . $value . "</td></tr>";
            }
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
