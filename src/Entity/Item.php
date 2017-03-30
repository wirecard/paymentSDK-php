<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Entity;

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
    private $amount;

    /**
     * @var Amount
     */
    private $taxAmount;

    /**
     * @var float
     */
    private $taxRate;

    /**
     * @var int
     */
    private $quantity;

    /**
     * Item constructor.
     * @param string $name
     * @param Amount $amount
     * @param int $quantity
     */
    public function __construct($name, Amount $amount, $quantity)
    {
        $this->name = $name;
        $this->amount = $amount;
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
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param Amount $taxAmount
     * @return Item
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getArticleNumber()
    {
        return $this->articleNumber;
    }

    /**
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return Amount
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $data['name'] = $this->getName();

        if (null !== $this->getDescription()) {
            $data['description'] = $this->getDescription();
        }

        if (null !== $this->getArticleNumber()) {
            $data['article-number'] = $this->getArticleNumber();
        }

        $data['amount'] = $this->getAmount()->mappedProperties();

        if (null !== $this->getTaxRate()) {
            $data['tax-rate'] = $this->getTaxRate();
        }

        if (null !== $this->getTaxAmount()) {
            $data['tax-amount'] = $this->getTaxAmount()->mappedProperties();
        }

        $data['quantity'] = $this->getQuantity();

        return $data;
    }
}
