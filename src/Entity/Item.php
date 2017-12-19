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
     * @param float $taxRate
     * @return Item
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
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

        if (null !== $this->description) {
            $data['description'] = $this->description;
        }

        if (null !== $this->articleNumber) {
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
     * @param array $data
     * @throws MandatoryFieldMissingException
     * @return array
     */
    private function payPalMappedProperties($data)
    {
        if (null !== $this->taxRate) {
            $taxAmountValue = number_format($this->price->getValue() * $this->quantity * ($this->taxRate / 100.0), 2);
            $taxAmount = new Amount($taxAmountValue, $this->price->getCurrency());

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
        if (null !== $this->taxRate) {
            $data['tax-rate'] = $this->taxRate;
        }

        return $data;
    }
}
