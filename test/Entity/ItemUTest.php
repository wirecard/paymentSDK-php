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
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;

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

    public function testMappedPropertiesOnlyRequiredProperties()
    {
        $expected = [
            'name' => self::NAME,
            'amount' => [
                'value' => '1',
                'currency' => 'EUR'
            ],
            'quantity' => self::QUANTITY,
        ];

        $this->assertEquals($expected, $this->item->mappedProperties());
    }

    public function testMappedPropertiesWithAllProperties()
    {
        $this->item->setArticleNumber('1232f5445');
        $this->item->setDescription('dthfbvdfg');
        $this->item->setTaxRate(20.0);

        $expected = [
            'name' => self::NAME,
            'description' => 'dthfbvdfg',
            'article-number' => '1232f5445',
            'amount' => [
                'value' => '1',
                'currency' => 'EUR'
            ],
            'quantity' => self::QUANTITY,
            'tax-rate' => 20.0
        ];

        $this->assertEquals($expected, $this->item->mappedProperties());
    }

    public function testMappedPropertiesWithAllPropertiesForPayPalWithRate()
    {
        $this->item->setVersion(PayPalTransaction::class);
        $this->item->setArticleNumber('1232f5445');
        $this->item->setDescription('dthfbvdfg');
        $this->item->setTaxRate(10);

        $expected = [
            'name' => self::NAME,
            'description' => 'dthfbvdfg',
            'article-number' => '1232f5445',
            'amount' => [
                'value' => 1,
                'currency' => 'EUR'
            ],
            'quantity' => self::QUANTITY,
            'tax-amount' => [
                'value' => 0.1,
                'currency' => 'EUR'
            ],
        ];

        $this->assertEquals($expected, $this->item->mappedProperties());
    }

    public function testMappedPropertiesWithAllPropertiesForPayPal()
    {
        $this->item->setVersion(PayPalTransaction::class);
        $this->item->setArticleNumber('1232f5445');
        $this->item->setDescription('dthfbvdfg');
        $this->item->setTaxAmount(new Amount(5, 'EUR'));

        $expected = [
            'name' => self::NAME,
            'description' => 'dthfbvdfg',
            'article-number' => '1232f5445',
            'amount' => [
                'value' => 1,
                'currency' => 'EUR'
            ],
            'quantity' => self::QUANTITY,
            'tax-amount' => [
                'value' => 5.00,
                'currency' => 'EUR'
            ],
        ];

        $this->assertEquals($expected, $this->item->mappedProperties());
    }

    public function testMappedSeamlessPropertiesWithAllProperties()
    {
        $this->item->setArticleNumber('1232f5445');
        $this->item->setTaxRate(10.0);

        $expected = [
            'orderItems1.name' => self::NAME,
            'orderItems1.articleNumber' => '1232f5445',
            'orderItems1.amount.value' => 1,
            'orderItems1.amount.currency' => 'EUR',
            'orderItems1.quantity' => self::QUANTITY,
            'orderItems1.taxRate' => 10.0,
        ];

        $this->assertEquals($expected, $this->item->mappedSeamlessProperties(1));
    }

    public function testGetArticleNumber()
    {
        $this->item->setArticleNumber('A1');

        $this->assertEquals('A1', $this->item->getArticleNumber());
    }
}
