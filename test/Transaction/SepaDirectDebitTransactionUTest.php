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
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class SepaDirectDebitTransactionUTest extends PHPUnit_Framework_TestCase
{
    const IBAN = 'DE42512308000000060004';
    const LAST_NAME = 'Doe';
    const FIRST_NAME = 'Jane';
    const MANDATE_ID = '2345';

    /**
     * @var SepaDirectDebitTransaction
     */
    private $tx;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var AccountHolder
     */
    private $accountHolder;

    public function setUp()
    {
        $this->amount = new Amount(55.5, 'EUR');
        $this->accountHolder = new AccountHolder();
        $this->accountHolder->setLastName(self::LAST_NAME);
        $this->accountHolder->setFirstName(self::FIRST_NAME);

        $this->tx = new SepaDirectDebitTransaction();
        $this->tx->setAmount($this->amount);
        $this->tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
    }

    public function testMappedPropertiesReserveIbanOnly()
    {
        $this->tx->setIban(self::IBAN);
        $this->tx->setAccountHolder($this->accountHolder);
        $this->tx->setNotificationUrl('notification url');
        $expectedResult = $this->getExpectedResultReserveIbanOnly();

        $this->tx->setOperation(Operation::RESERVE);
        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testMappedPropertiesReserveIbanAndBic()
    {
        $this->tx->setIban(self::IBAN);
        $this->tx->setAccountHolder($this->accountHolder);
        $bic = '42B';
        $this->tx->setBic($bic);
        $this->tx->setNotificationUrl('notification url');

        $expectedResult = $this->getExpectedResultReserveIbanOnly();
        $expectedResult['bank-account']['bic'] = $bic;

        $this->tx->setOperation(Operation::RESERVE);
        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    private function getExpectedResultReserveIbanOnly()
    {
        return [
            'transaction-type' => 'authorization',
            'notifications' => ['notification' => [['url' => 'notification url']]],
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '55.5'
            ],
            'account-holder' => [
                'last-name' => self::LAST_NAME,
                'first-name' => self::FIRST_NAME
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'sepadirectdebit'
                    ]
                ]
            ],
            'bank-account' => [
                'iban' => self::IBAN
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
            'ip-address' => '0.0.0.1'
        ];
    }

    public function testMappedPropertiesPayIbanOnly()
    {
        $this->tx->setIban(self::IBAN);
        $this->tx->setAccountHolder($this->accountHolder);

        $mandate = new Mandate(self::MANDATE_ID);
        $this->tx->setMandate($mandate);

        $expectedResult = $this->getExpectedResultPayIbanOnly();

        $this->tx->setOperation(Operation::PAY);
        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    private function getExpectedResultPayIbanOnly()
    {
        return [
            'transaction-type' => Transaction::TYPE_DEBIT,
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '55.5'
            ],
            'account-holder' => [
                'last-name' => self::LAST_NAME,
                'first-name' => self::FIRST_NAME
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'sepadirectdebit'
                    ]
                ]
            ],
            'bank-account' => [
                'iban' => self::IBAN
            ],
            'mandate' => [
                'mandate-id' => self::MANDATE_ID,
                'signed-date' => $this->today()
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
            'ip-address' => '0.0.0.1'
        ];
    }


    public function testMappedPropertiesCancelPay()
    {
        $parentTransactionId = 'B612';
        $this->tx->setParentTransactionId($parentTransactionId);
        $this->tx->setParentTransactionType('pending-debit');
        $this->tx->setOperation(Operation::CANCEL);

        $result = $this->tx->mappedProperties();

        $expectedResult = $this->getExpectedResultCancelPay($parentTransactionId);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMappedPropertiesUnsupportedOperation()
    {
        $this->tx->setOperation('non_existing_operation');
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testGetRetrieveTransactionTypeCancelWithoutParentTransactionThrowsException()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMappedPropertiesUnsupportedCancelOperation()
    {
        $this->tx->setParentTransactionId('1');
        $this->tx->setParentTransactionType('authorization');
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->mappedProperties();
    }

    private function getExpectedResultCancelPay($parentTransactionId)
    {
        return [
            'transaction-type' => 'void-pending-debit',
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '55.5'
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'sepadirectdebit'
                    ]
                ]
            ],
            'parent-transaction-id' => $parentTransactionId,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'ip-address' => '0.0.0.1'
        ];
    }

    public function testDescriptor()
    {
        $descriptor = "0123-€\$§%!=#~;+/?:().,'&><\"*{}[]@\_°^|ÄÖÜäöüß^°`" .
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123lorem ipsum try to get more than 100 characters";
        // Only 100 chars are allowed
        $expectedDescriptor = "0123-€\$§%!=#~;+/?:().,'&><\"*{}[]@\_°^|ÄÖÜäöüß^°`" .
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $transaction = new SepaDirectDebitTransaction();
        $transaction->setDescriptor($descriptor);
        $this->assertEquals($expectedDescriptor, $transaction->getDescriptor());
    }

    /**
     * @return false|string
     */
    private function today()
    {
        return gmdate('Y-m-d');
    }
}
