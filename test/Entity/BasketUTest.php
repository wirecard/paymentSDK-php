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
 * for customized shop systems or installed SDK of other vendors of SDK within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
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
}
