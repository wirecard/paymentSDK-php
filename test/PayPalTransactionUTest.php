<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\PayPalTransaction;

class PayPalTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const AMOUNT = 42.21;
    const EUR = 'EUR';
    const NOTIFICATION_URL = 'http://www.example.com';

    /**
     * @var PayPalTransaction
     */
    private $payPalTransaction;

    public function setUp()
    {
        $this->payPalTransaction = new PayPalTransaction(self::AMOUNT, self::EUR, self::NOTIFICATION_URL);
    }

    public function testGetAmount()
    {
        $this->assertEquals(self::AMOUNT, $this->payPalTransaction->getAmount());
    }

    public function testGetCurrency()
    {
        $this->assertEquals(self::EUR, $this->payPalTransaction->getCurrency());
    }

    public function testGetNotificationUrl()
    {
        $this->assertEquals(self::NOTIFICATION_URL, $this->payPalTransaction->getNotificationUrl());
    }
}
