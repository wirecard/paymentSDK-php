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

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class SepaTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const IBAN = 'DE42512308000000060004';
    const LAST_NAME = 'Doe';
    const FIRST_NAME = 'Jane';
    const MANDATE_ID = '2345';

    /**
     * @var SepaTransaction
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

        $this->tx = new SepaTransaction();
        $this->tx->setAmount($this->amount);
    }

    public function testMappedPropertiesReserveIbanOnly()
    {
        $this->tx->setIban(self::IBAN);
        $this->tx->setAccountHolder($this->accountHolder);
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

    public function testRetrievePaymentMethodNamePay()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->assertEquals(SepaTransaction::DIRECT_DEBIT, $this->tx->getConfigKey());
    }

    public function testRetrievePaymentMethodNameCredit()
    {
        $this->tx->setOperation(Operation::CREDIT);
        $this->assertEquals(SepaTransaction::CREDIT_TRANSFER, $this->tx->getConfigKey());
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
        ];
    }

    /**
     * @return false|string
     */
    private function today()
    {
        return gmdate('Y-m-d');
    }

    public function testRetrieveTransactionTypeForCredit()
    {
        $this->tx->setOperation(Operation::CREDIT);
        $data = $this->tx->mappedProperties();

        $this->assertEquals(Transaction::TYPE_CREDIT, $data['transaction-type']);
    }
}
