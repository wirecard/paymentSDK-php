<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Amount;

class BasketUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Basket
     */
    private $itemCollection;

    public function setUp()
    {
        $this->itemCollection = new Basket();
    }

    public function testAdd()
    {
        $item = new Item('test', new Amount(1, 'EUR'), 1);
        $this->itemCollection->add($item);

        $this->assertAttributeEquals([$item], 'items', $this->itemCollection);
    }

    public function testGetIterator()
    {
        $this->assertEquals(new \ArrayIterator([]), $this->itemCollection->getIterator());
    }

    public function testMappedProperties()
    {
        $item = new Item('test item name', new Amount(1, 'EUR'), 1);
        $this->itemCollection->add($item);

        $expected = [
            'order-item' => [
                [
                    'name' => 'test item name',
                    'amount' => [
                        'value' => '1',
                        'currency' => 'EUR'
                    ],
                    'quantity' => '1'
                ]
            ]
        ];
        $this->assertEquals($expected, $this->itemCollection->mappedProperties());
    }

    public function testMappedPropertiesMultiple()
    {
        $item = new Item('test item name', new Amount(1, 'EUR'), 1);
        $item->setTaxRate(12);
        $this->itemCollection->add($item);
        $this->itemCollection->add($item);

        $expected = [
            'order-item' => [
                [
                    'name' => 'test item name',
                    'amount' => [
                        'value' => '1',
                        'currency' => 'EUR'
                    ],
                    'quantity' => '2',
                    'tax-rate' => 12
                ]
            ]
        ];
        $this->assertEquals($expected, $this->itemCollection->mappedProperties());
    }

    public function testMappedSeamlessProperties()
    {
        $item = new Item('test item name', new Amount(1, 'EUR'), 1);
        $this->itemCollection->add($item);

        $expected = [
            'orderItems1.name' => 'test item name',
            'orderItems1.amount.value' =>  '1',
            'orderItems1.amount.currency' => 'EUR',
            'orderItems1.quantity' => '1'
        ];
        $this->assertEquals($expected, $this->itemCollection->mappedSeamlessProperties());
    }

    public function testGetAsHtml()
    {
        $item = new Item('test item name', new Amount(1, 'EUR'), 1);
        $this->itemCollection->add($item);
        $this->assertNotEmpty($this->itemCollection->getAsHtml(['table_id' => 'myid']));
    }

    public function testGetTotalAmount()
    {
        $basket = new Basket();
        $item = new Item('test item name', new Amount(1, 'EUR'), 1);
        $item2 = new Item('test item name 2', new Amount(2, 'EUR'), 2);
        $basket->add($item)->add($item2);

        $this->assertEquals(new Amount(5, 'EUR'), $basket->getTotalAmount());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetTotalAmountError()
    {
        $basket = new Basket();
        $item = new Item('test item name', new Amount(1, 'EUR'), 1);
        $item2 = new Item('test item name 2', new Amount(2, 'USD'), 2);
        $basket->add($item)->add($item2);

        $basket->getTotalAmount();
    }
}
