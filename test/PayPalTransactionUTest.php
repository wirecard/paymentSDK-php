<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Money;
use Wirecard\PaymentSdk\PayPalTransaction;
use Wirecard\PaymentSdk\Redirect;

class PayPalTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const NOTIFICATION_URL = 'http://www.example.com';
    /**
     * @var Money
     */
    private $amount;

    /**
     * @var PayPalTransaction
     */
    private $payPalTransaction;

    /**
     * @var Redirect
     */
    private $redirect;

    public function setUp()
    {
        $this->amount = new Money(42.21, 'EUR');
    }

    public function testConstructorWithRedirect()
    {
        $this->redirect = new Redirect('http://www.example.com/success', 'http://www.example.com/cancel');
        $this->payPalTransaction = new PayPalTransaction($this->amount, self::NOTIFICATION_URL, $this->redirect);

        $this->assertEquals($this->amount, $this->payPalTransaction->getAmount());
        $this->assertEquals(self::NOTIFICATION_URL, $this->payPalTransaction->getNotificationUrl());
        $this->assertEquals($this->redirect, $this->payPalTransaction->getRedirect());
    }

    public function testConstructorWithoutRedirect()
    {
        $this->payPalTransaction = new PayPalTransaction($this->amount, self::NOTIFICATION_URL);

        $this->assertEquals($this->amount, $this->payPalTransaction->getAmount());
        $this->assertEquals(self::NOTIFICATION_URL, $this->payPalTransaction->getNotificationUrl());
        $this->assertEquals(null, $this->payPalTransaction->getRedirect());
    }
}
