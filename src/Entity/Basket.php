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

use Traversable;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use SimpleXMLElement;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayTransaction;

/**
 * Class Basket
 * @package Wirecard\PaymentSdk\Entity
 */
class Basket implements \IteratorAggregate, MappableEntity
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @var string
     */
    private $version;

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @param Item $item
     * @return $this
     */
    public function add(Item $item)
    {
        $itemMapped = $item->mappedProperties();
        $itemMapped['quantity'] = 0;

        $foundKey = -1;
        $foundQuantity = 0;

        foreach ($this->items as $key => $currentItem) {
            /**
             * @var Item $currentItem
             */
            $currentItemMapped = $currentItem->mappedProperties();
            $currentItemMapped['quantity'] = 0;
            if ($currentItemMapped == $itemMapped) {
                $foundKey = $key;
                $foundQuantity = $currentItem->getQuantity();
                break;
            }
        }

        if ($foundKey != -1) {
            /** @var Item $foundItem */
            $foundItem = $this->items[$foundKey];

            $foundItem->setQuantity($foundQuantity + $item->getQuantity());
        } else {
            $this->items[] = $item;
        }

        return $this;
    }

    /**
     * @throws MandatoryFieldMissingException
     * @return array
     */
    public function mappedProperties()
    {
        $data = ['order-item' => []];

        /**
         * @var Item $item
         */
        foreach ($this->getIterator() as $item) {
            $item->setVersion($this->version);
            $data['order-item'][] = $item->mappedProperties();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function mappedSeamlessProperties()
    {
        $basket = array();
        $quantity = 1;

        /**
         * @var Item $item
         */
        foreach ($this->getIterator() as $item) {
            $basket = $basket + $item->mappedSeamlessProperties($quantity);
            $quantity++;
        }

        return $basket;
    }

    /**
     * Parse simplexml and create basket object
     * @param SimpleXMLElement $simpleXml
     * @return Basket
     * @since 3.2.0
     */
    public function parseFromXml($simpleXml)
    {
        if (!isset($simpleXml->{'order-items'})) {
            return;
        }

        if ($simpleXml->{'order-items'}->children()->count() < 1) {
            return;
        }

        $basketVersion = '';
        switch ((string)$simpleXml->{'payment-methods'}->{'payment-method'}['name']) {
            case PayPalTransaction::NAME:
                $basketVersion = PayPalTransaction::class;
                break;
            case RatepayInvoiceTransaction::NAME:
            case RatepayInstallmentTransaction::NAME:
                $basketVersion = RatepayTransaction::class;
                break;
        }

        foreach ($simpleXml->{'order-items'}->children() as $orderItem) {
            $amountAttrs = $orderItem->amount->attributes();
            $amount = new Amount(
                (float)$orderItem->amount,
                (string)$amountAttrs->currency
            );

            $basketItem = new Item((string)$orderItem->name, $amount, (int)$orderItem->quantity);

            if (isset($orderItem->{'tax-amount'})) {
                $taxAmountAttrs = $orderItem->{'tax-amount'}->attributes();
                $taxAmount = new Amount(
                    (float)$orderItem->{'tax-amount'},
                    (string)$taxAmountAttrs->currency
                );
                $basketItem->setTaxAmount($taxAmount);
            }
            $basketItem->setVersion($basketVersion)
                ->setDescription((string)$orderItem->description)
                ->setArticleNumber((string)$orderItem->{'article-number'});

            $this->add($basketItem);
        }

        return $this;
    }

    /**
     * @param $options
     *      table_id,
     *      table_class,
     *      translation
     *          basket,
     *          item
     * @return string
     * @since 3.2.0
     */
    public function getAsHtml($options = [])
    {
        $defaults = [
            'table_id' => null,
            'table_class' => null,
            'translations' => [
                'basket' => 'Basket',
                'item' => 'Item'
            ]
        ];

        $options = array_merge($defaults, $options);
        $translation = $options['translations'];

        $html = "<table id='{$options['table_id']}' class='{$options['table_class']}'><tbody>";

        /** @var Item $item */
        $itemNumber = 1;
        foreach ($this->getIterator() as $item) {
            $itemProperties = $item->mappedProperties();
            $html .= "<tr id='{$options['table_id']}_otherrows'>";
            $html .= "<td valign='top' rowspan='" . count($itemProperties) . "'>";
            $html .= "{$this->translate('item', $translation)} #$itemNumber</td>";
            $attrIter = 0;
            foreach ($itemProperties as $key => $value) {
                // this is for the amount object
                if (in_array($key, ['amount', 'tax-amount']) && isset($value['currency']) && isset($value['value'])) {
                    $value = "{$value['currency']} {$value['value']}";
                }
                if ($attrIter++ != 0) {
                    $html .= "</tr><tr>";
                }
                $html .= "<td>" . $this->translate($key, $translation) . "</td><td>$value</td>";
            }

            $itemNumber++;
            $html .= "</tr>";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * @since 3.0.0
     * @return Amount
     */
    public function getTotalAmount()
    {
        $amount = 0;
        $currency = '';
        /** @var Item $item */
        foreach ($this->getIterator() as $item) {
            $amount += floatval($item->getPrice()->getValue()) * $item->getQuantity();
            if (strlen($currency) == 0) {
                $currency = $item->getPrice()->getCurrency();
            } elseif ($currency !== $item->getPrice()->getCurrency()) {
                throw new \InvalidArgumentException('You cannot have different currencies in your basket.');
            }
        }

        return new Amount($amount, $currency);
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
        if (!is_null($translations) && isset($translations[$key])) {
            return $translations[$key];
        }

        return $key;
    }
}
