<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\CreditCardTransaction;
use Wirecard\PaymentSdk\Money;

class CreditCardTransactionUTest extends \PHPUnit_Framework_TestCase
{
    private $ccTransaction;

    private $amount;

    const SAMPLE_TRANSACTION_ID = '542';

    public function setUp()
    {
        $this->amount = new Money(8.5, 'EUR');
        $this->ccTransaction = new CreditCardTransaction($this->amount, self::SAMPLE_TRANSACTION_ID);
    }

    public function testGetAmount()
    {
        $this->assertEquals($this->amount, $this->ccTransaction->getAmount());
    }

    public function testGetTransactionId()
    {
        $this->assertEquals(self::SAMPLE_TRANSACTION_ID, $this->ccTransaction->getTransactionId());
    }
}
