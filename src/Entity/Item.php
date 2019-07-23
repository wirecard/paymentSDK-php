<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayTransaction;

/**
 * Class Item
 * @package Wirecard\PaymentSdk\Entity
 */
class Item implements MappableEntity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $articleNumber;

    /**
     * @var Amount
     */
    private $price;

    /**
     * @var float
     */
    private $taxRate;

    /**
     * @var Amount
     */
    private $taxAmount;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $version;

    /**
     * Item constructor.
     * @param string $name
     * @param Amount $price
     * @param int $quantity
     */
    public function __construct($name, Amount $price, $quantity)
    {
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @since 3.0.0
     * @return Amount
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @since 3.0.0
     * @return string
     */
    public function getArticleNumber()
    {
        return $this->articleNumber;
    }

    /**
     * @param string $description
     * @return Item
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $articleNumber
     * @return Item
     */
    public function setArticleNumber($articleNumber)
    {
        $this->articleNumber = $articleNumber;
        return $this;
    }

    /**
     * @param int $quantity
     * @return Item
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @param float $taxRate
     * @return Item
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    /**
     * @param Amount $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    /**
     * @param string $version
     * @return Item
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @throws MandatoryFieldMissingException
     * @return array
     */
    public function mappedProperties()
    {
        $data['name'] = $this->name;
        $data['quantity'] = $this->quantity;
        $data['amount'] = $this->price->mappedProperties();

        if (!is_null($this->description)) {
            $data['description'] = $this->description;
        }

        if (!is_null($this->articleNumber)) {
            $data['article-number'] = $this->articleNumber;
        }

        switch ($this->version) {
            case PayPalTransaction::class:
                $data = $this->payPalMappedProperties($data);
                break;
            case RatepayTransaction::class:
            case RatepayInstallmentTransaction::class:
            case RatepayInvoiceTransaction::class:
            default:
                $data = $this->ratepayMappedProperties($data);
        }

        return $data;
    }

    /**
     * @param integer $iterator
     * @return array
     */
    public function mappedSeamlessProperties($iterator)
    {
        $item = array();
        $item['orderItems' . $iterator . '.name'] = $this->name;
        $item['orderItems' . $iterator . '.quantity'] = $this->quantity;
        $item['orderItems' . $iterator . '.amount.value'] = $this->price->getValue();
        $item['orderItems' . $iterator . '.amount.currency'] = $this->price->getCurrency();

        if (!is_null($this->articleNumber)) {
            $item['orderItems' . $iterator . '.articleNumber'] = $this->articleNumber;
        }

        if (!is_null($this->taxRate)) {
            $item['orderItems' . $iterator . '.taxRate'] = $this->taxRate;
        }

        return $item;
    }

    /**
     * @param array $data
     * @throws MandatoryFieldMissingException
     * @return array
     */
    private function payPalMappedProperties($data)
    {
        if (!is_null($this->taxAmount)) {
            $data['tax-amount'] = $this->taxAmount->mappedProperties();
        } elseif (!is_null($this->taxRate)) {
            $taxAmountValue = number_format($this->price->getValue() * $this->quantity * ($this->taxRate / 100.0), 2);
            $taxAmount = new Amount((float) $taxAmountValue, $this->price->getCurrency());

            $data['tax-amount'] = $taxAmount->mappedProperties();
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function ratepayMappedProperties($data)
    {
        if (!is_null($this->taxRate)) {
            $data['tax-rate'] = $this->taxRate;
        }

        return $data;
    }
}
