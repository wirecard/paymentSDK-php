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
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;

class PoiPiaTransactionUTest extends PHPUnit_Framework_TestCase
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
        $this->tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
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
            'ip-address' => '0.0.0.1'
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
            'ip-address' => '0.0.0.1'
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
