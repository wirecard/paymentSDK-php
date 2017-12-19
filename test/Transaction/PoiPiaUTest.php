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
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;

class PoiPiaTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const LAST_NAME = 'Doe';
    const FIRST_NAME = 'John';
    const EMAIL = 'john.doe@wirecard.com';

    /**
     * @var PoiPiaTransaction
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
        $this->accountHolder->setEmail(self::EMAIL);

        $this->tx = new PoiPiaTransaction();
        $this->tx->setAmount($this->amount);
    }

    public function testMappedPropertiesReserve()
    {
        $this->tx->setAccountHolder($this->accountHolder);
        $expectedResult = $this->getExpectedResultReserve();

        $this->tx->setOperation(Operation::RESERVE);
        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    private function getExpectedResultReserve()
    {
        return [
            'transaction-type' => Transaction::TYPE_AUTHORIZATION,
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '55.5'
            ],
            'account-holder' => [
                'last-name' => self::LAST_NAME,
                'first-name' => self::FIRST_NAME,
                'email' => self::EMAIL
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'wiretransfer'
                    ]
                ]
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
    }

    public function testGetEndpointCancel()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->assertEquals(Transaction::ENDPOINT_PAYMENTS, $this->tx->getEndpoint());
    }

    public function testGetEndpoint()
    {
        $this->tx->setOperation(Operation::RESERVE);
        $this->assertEquals(Transaction::ENDPOINT_PAYMENT_METHODS, $this->tx->getEndpoint());
    }

    public function testMappedPropertiesCancelPay()
    {
        $parentTransactionId = 'B612';
        $this->tx->setParentTransactionId($parentTransactionId);
        $this->tx->setParentTransactionType(Transaction::TYPE_AUTHORIZATION);
        $this->tx->setOperation(Operation::CANCEL);

        $result = $this->tx->mappedProperties();

        $expectedResult = $this->getExpectedResultCancelPay($parentTransactionId);
        $this->assertEquals($expectedResult, $result);
    }

    private function getExpectedResultCancelPay($parentTransactionId)
    {
        return [
            'transaction-type' => Transaction::TYPE_VOID_AUTHORIZATION,
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '55.5'
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'wiretransfer'
                    ]
                ]
            ],
            'parent-transaction-id' => $parentTransactionId,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
        ];
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
        $this->tx->setParentTransactionType(Transaction::TYPE_DEBIT);
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->mappedProperties();
    }

    public function testRetrievePaymentMethodNameReserve()
    {
        $this->tx->setOperation(Operation::RESERVE);
        $this->assertEquals(PoiPiaTransaction::NAME, $this->tx->getConfigKey());
    }

    public function testRetrieveTransactionTypeForCancel()
    {
        $this->tx->setParentTransactionId("1234");
        $this->tx->setParentTransactionType(Transaction::TYPE_AUTHORIZATION);
        $this->tx->setOperation(Operation::CANCEL);
        $data = $this->tx->mappedProperties();

        $this->assertEquals(Transaction::TYPE_VOID_AUTHORIZATION, $data['transaction-type']);
    }

    /**
     * @return false|string
     */
    private function today()
    {
        return gmdate('Y-m-d');
    }
}
