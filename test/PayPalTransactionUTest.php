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
        $this->redirect = new Redirect('http://www.example.com/success', 'http://www.example.com/cancel');
        $this->payPalTransaction = new PayPalTransaction($this->amount, self::NOTIFICATION_URL, $this->redirect);
    }

    public function testGetAmount()
    {
        $this->assertEquals($this->amount, $this->payPalTransaction->getAmount());
    }

    public function testGetNotificationUrl()
    {
        $this->assertEquals(self::NOTIFICATION_URL, $this->payPalTransaction->getNotificationUrl());
    }

    public function testGetRedirect()
    {
        $this->assertEquals($this->redirect, $this->payPalTransaction->getRedirect());
    }
}
