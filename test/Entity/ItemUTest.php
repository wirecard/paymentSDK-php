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
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Entity\Amount;

class ItemUTest extends \PHPUnit_Framework_TestCase
{
    const NAME = 'Item xsfgdgh';
    const QUANTITY = 1;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var Item
     */
    private $item;

    public function setUp()
    {
        $this->amount = new Amount(1, 'EUR');
        $this->item = new Item(self::NAME, $this->amount, self::QUANTITY);
    }

    public function testGetName()
    {
        $this->assertEquals(self::NAME, $this->item->getName());
    }

    public function testGetAmount()
    {
        $this->assertEquals($this->amount, $this->item->getAmount());
    }

    public function testGetQuantity()
    {
        $this->assertEquals(self::QUANTITY, $this->item->getQuantity());
    }

    public function testSetDescription()
    {
        $desc = 'My desc';
        $this->item->setDescription($desc);
        $this->assertAttributeEquals($desc, 'description', $this->item);
    }

    public function testGetDescription()
    {
        $desc = 'My desc';
        $this->item->setDescription($desc);
        $this->assertEquals($desc, $this->item->getDescription());
    }

    public function testSetArticleNumber()
    {
        $number = '451541';
        $this->item->setArticleNumber($number);
        $this->assertAttributeEquals($number, 'articleNumber', $this->item);
    }

    public function testGetArticleNumber()
    {
        $number = '451541';
        $this->item->setArticleNumber($number);
        $this->assertEquals($number, $this->item->getArticleNumber());
    }

    public function testSetTaxAmount()
    {
        $this->item->setTaxAmount($this->amount);
        $this->assertAttributeEquals($this->amount, 'taxAmount', $this->item);
    }

    public function testGetTaxAmount()
    {
        $this->item->setTaxAmount($this->amount);
        $this->assertEquals($this->amount, $this->item->getTaxAmount());
    }

    public function testSetTaxRate()
    {
        $rate = '0.2';
        $this->item->setTaxRate($rate);
        $this->assertAttributeEquals($rate, 'taxRate', $this->item);
    }

    public function testGetTaxRate()
    {
        $rate = '0.2';
        $this->item->setTaxRate($rate);
        $this->assertEquals($rate, $this->item->getTaxRate());
    }

    public function testMappedProperties()
    {
        $this->item->setTaxAmount($this->amount);
        $this->item->setArticleNumber('1232f5445');
        $this->item->setDescription('dthfbvdfg');
        $this->item->setTaxRate('0.2');

        $expected = [
            'name' => $this->item->getName(),
            'description' => $this->item->getDescription(),
            'article-number' => $this->item->getArticleNumber(),
            'amount' => [
                'value' => $this->item->getAmount()->getValue(),
                'currency' => $this->item->getAmount()->getCurrency()
            ],
            'tax-amount' => [
                'value' => $this->item->getTaxAmount()->getValue(),
                'currency' => $this->item->getTaxAmount()->getCurrency()
            ],
            'quantity' => $this->item->getQuantity(),
            'tax-rate' => '0.2'
        ];

        $this->assertEquals($expected, $this->item->mappedProperties());
    }
}
