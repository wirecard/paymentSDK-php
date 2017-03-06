<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;

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
     * @var Money
     */
    private $amount;

    /**
     * @var AccountHolder
     */
    private $accountHolder;

    public function setUp()
    {
        $this->amount = new Money(55.5, 'EUR');
        $this->accountHolder = new AccountHolder(self::LAST_NAME);
        $this->accountHolder->setFirstName(self::FIRST_NAME);

        $this->tx = new SepaTransaction();
        $this->tx->setAmount($this->amount);
    }

    public function testMappedPropertiesReserveIbanOnly()
    {
        $this->tx->setIban(self::IBAN);
        $this->tx->setAccountHolder($this->accountHolder);

        $expectedResult = $this->getExpectedResultReserveIbanOnly();

        $result = $this->tx->mappedProperties(Operation::RESERVE, null);

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

        $result = $this->tx->mappedProperties(Operation::RESERVE, null);

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
            ]
        ];
    }

    public function testMappedPropertiesPayIbanOnly()
    {
        $this->tx->setIban(self::IBAN);
        $this->tx->setAccountHolder($this->accountHolder);

        $mandate = new Mandate(self::MANDATE_ID);
        $this->tx->setMandate($mandate);

        $expectedResult = $this->getExpectedResultPayIbanOnly();

        $result = $this->tx->mappedProperties(Operation::PAY, null);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    private function getExpectedResultPayIbanOnly()
    {
        return [
            'transaction-type' => 'pending-debit',
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
            ]
        ];
    }


    public function testMappedPropertiesCancelPay()
    {
        $parentTransactionId = 'B612';
        $this->tx->setParentTransactionId($parentTransactionId);

        $result = $this->tx->mappedProperties(Operation::CANCEL, 'pending-debit');

        $expectedResult = $this->getExpectedResultCancelPay($parentTransactionId);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMappedPropertiesUnsupportedOperation()
    {
        $this->tx->mappedProperties('non_existing_operation');
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMappedPropertiesUnsupportedCancelOperation()
    {
        $this->tx->mappedProperties(Operation::CANCEL, 'authorization');
    }

    public function testRetrievePaymentMethodNamePay()
    {
        $this->assertEquals(SepaTransaction::DIRECT_DEBIT, $this->tx->retrievePaymentMethodName(Operation::PAY));
        $this->assertEquals(SepaTransaction::DIRECT_DEBIT, $this->tx->getConfigKey(Operation::PAY));
    }

    public function testRetrievePaymentMethodNameCredit()
    {
        $this->assertEquals(SepaTransaction::CREDIT_TRANSFER, $this->tx->retrievePaymentMethodName(Operation::CREDIT));
        $this->assertEquals(SepaTransaction::CREDIT_TRANSFER, $this->tx->getConfigKey(Operation::CREDIT));
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
            'parent-transaction-id' => $parentTransactionId
        ];
    }

    /**
     * @return false|string
     */
    private function today()
    {
        return gmdate('Y-m-d');
    }
}
