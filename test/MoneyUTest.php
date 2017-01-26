<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Money;

class MoneyUTest extends \PHPUnit_Framework_TestCase
{
    const AMOUNT = 42.21;
    const EUR = 'EUR';

    /**
     * @var Money
     */
    private $money;

    public function setUp()
    {
        $this->money = new Money(self::AMOUNT, self::EUR);
    }

    public function testGetAmount()
    {
        $this->assertEquals(self::AMOUNT, $this->money->getAmount());
    }

    public function testGetCurrency()
    {
        $this->assertEquals(self::EUR, $this->money->getCurrency());
    }
}
