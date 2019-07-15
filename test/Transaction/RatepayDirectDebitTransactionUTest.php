<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Transaction;

use PHPUnit_Framework_TestCase;
use Wirecard\PaymentSdk\Entity\BankAccount;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\RatepayDirectDebitTransaction;

class RatepayDirectDebitTransactionUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RatepayDirectDebitTransaction
     */
    private $tx;

    public function setUp()
    {
        $this->tx = new RatepayDirectDebitTransaction();
        $this->tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
    }

    public function testSetCreditorId()
    {
        $this->tx->setCreditorId('creditor id');
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->setBankAccount(new BankAccount());
        $this->tx->setMandate(new Mandate('mandate id'));

        $this->assertEquals('creditor id', $this->tx->mappedProperties()['creditor-id']);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testBankAccountMandatory()
    {
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->mappedProperties();
    }
}
