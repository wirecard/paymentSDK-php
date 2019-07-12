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
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Transaction;

use PHPUnit_Framework_TestCase;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class SepaCreditTransferTransactionUTest extends PHPUnit_Framework_TestCase
{
    const IBAN = 'DE42512308000000060004';
    const LAST_NAME = 'Doe';
    const FIRST_NAME = 'Jane';
    const MANDATE_ID = '2345';

    /**
     * @var SepaCreditTransferTransaction
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

        $mandate = new Mandate(self::MANDATE_ID);

        $this->tx = new SepaCreditTransferTransaction();
        $this->tx->setMandate($mandate);
        $this->tx->setAmount($this->amount);
        $this->tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
    }

    public function testMappedPropertiesCreditIbanOnly()
    {
        $this->tx->setIban(self::IBAN);
        $this->tx->setAccountHolder($this->accountHolder);
        $this->tx->setNotificationUrl('notification url');
        $expectedResult = $this->getExpectedResultCreditIbanOnly();

        $this->tx->setOperation(Operation::CREDIT);
        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testMappedPropertiesCreditIbanAndBic()
    {
        $this->tx->setIban(self::IBAN);
        $this->tx->setAccountHolder($this->accountHolder);
        $bic = '42B';
        $this->tx->setBic($bic);
        $this->tx->setNotificationUrl('notification url');

        $expectedResult = $this->getExpectedResultCreditIbanOnly();
        $expectedResult['bank-account']['bic'] = $bic;

        $this->tx->setOperation(Operation::CREDIT);
        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    private function getExpectedResultCreditIbanOnly()
    {
        return [
            'transaction-type' => Transaction::TYPE_CREDIT,
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
                        'name' => 'sepacredit'
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


    public function testMappedPropertiesCancelCredit()
    {
        $parentTransactionId = 'B612';
        $this->tx->setParentTransactionId($parentTransactionId);
        $this->tx->setParentTransactionType('pending-credit');
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
            'transaction-type' => 'void-pending-credit',
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '55.5'
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'sepacredit'
                    ]
                ]
            ],
            'parent-transaction-id' => $parentTransactionId,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'ip-address' => '0.0.0.1'
        ];
    }

    public function testRetrieveTransactionTypeForCredit()
    {
        $this->tx->setOperation(Operation::CREDIT);
        $data = $this->tx->mappedProperties();

        $this->assertEquals(Transaction::TYPE_CREDIT, $data['transaction-type']);
    }

    public function testDescriptor()
    {
        $descriptor = "0123-€\$§%!=#~;+/?:().,'&><\"*{}[]@\_°^|ÄÖÜäöüß^°`" .
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123lorem ipsum try to get more than 100 characters";
        // Only 100 chars are allowed
        $expectedDescriptor = "0123-€\$§%!=#~;+/?:().,'&><\"*{}[]@\_°^|ÄÖÜäöüß^°`" .
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $transaction = new SepaCreditTransferTransaction();
        $transaction->setDescriptor($descriptor);
        $this->assertEquals($expectedDescriptor, $transaction->getDescriptor());
    }
}
