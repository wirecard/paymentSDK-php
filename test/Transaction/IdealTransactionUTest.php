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
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

class IdealTransactionUTest extends PHPUnit_Framework_TestCase
{
    const BANK = 'ING';
    const SUCCESS_URL = 'http://www.example.com/?status=success';
    const CANCEL_URL = 'http://www.example.com/cancel';
    const DESCRIPTOR = 'dummy description';

    /**
     * @var IdealTransaction
     */
    private $tx;

    public function setUp()
    {
        $redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL);
        $this->tx = new IdealTransaction();
        $this->tx->setRedirect($redirect);
        $this->tx->setBic(self::BANK);
        $this->tx->setAmount(new Amount(33, 'USD'));
        $this->tx->setDescriptor(self::DESCRIPTOR);
        $this->tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testSetBicThrowsUnsupportedBank()
    {
        $this->tx->setBic('asdf');
    }

    public function testMappedProperties()
    {
        $expectedResult = [
            'transaction-type' => Transaction::TYPE_DEBIT,
            'requested-amount' => [
                'currency' => 'USD',
                'value' => '33'
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => IdealTransaction::NAME
                    ]
                ]
            ],
            'cancel-redirect-url' => self::CANCEL_URL,
            'success-redirect-url' => self::SUCCESS_URL . '&request_id=',
            'bank-account' => ['bic' => 'INGBNL2A'],
            'descriptor' => self::DESCRIPTOR,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'ip-address' => '0.0.0.1'
        ];

        $this->tx->setOperation(Operation::PAY);

        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testDescriptor()
    {
        $descriptor = "0123-€\$§%!=#~;+/?:().,'&><\"*{}[]@\_°^ |ÄÖÜäöüß°`abcdefghijklmn" .
            "opqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        // Only 35 chars are allowed
        $expectedDescriptor = "0123-+.,' ÄÖÜäöüabcdefghijklmnopqrs";
        $transaction = new IdealTransaction();
        $transaction->setDescriptor($descriptor);
        $this->assertEquals($expectedDescriptor, $transaction->getDescriptor());
    }
}
