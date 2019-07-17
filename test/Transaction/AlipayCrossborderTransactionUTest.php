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
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\AlipayCrossborderTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class AlipayCrossborderTransactionUTest extends PHPUnit_Framework_TestCase
{
    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';

    /**
     * @var AlipayCrossborderTransaction
     */
    private $tx;

    public function setUp()
    {
        $redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL);
        $accountHolder = new AccountHolder();
        $accountHolder->setFirstName("Firstname");
        $accountHolder->setLastName("Lastname");
        $this->tx = new AlipayCrossborderTransaction();
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount(new Amount(45, 'EUR'));
        $this->tx->setAccountHolder($accountHolder);
        $this->tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
    }

    public function testGetEndpointPayments()
    {
        $this->tx->setOperation(Operation::CANCEL);

        $this->assertEquals(Transaction::ENDPOINT_PAYMENTS, $this->tx->getEndpoint());
    }

    public function testGetEndpointPaymentmethods()
    {
        $this->tx->setOperation(Operation::PAY);

        $this->assertEquals(Transaction::ENDPOINT_PAYMENT_METHODS, $this->tx->getEndpoint());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testOnlyDebitCanBeRefunded()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setParentTransactionId('parent tx id');
        $this->tx->setParentTransactionType(Operation::RESERVE);

        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMandatoryParentTxForCancel()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->mappedProperties();
    }

    public function testMappedProperties()
    {
        $expectedResult = [
            'transaction-type' => Transaction::TYPE_DEBIT,
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '45'
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'alipay-xborder'
                    ]
                ]
            ],
            'cancel-redirect-url' => self::CANCEL_URL,
            'success-redirect-url' => self::SUCCESS_URL,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'account-holder' => [
                'first-name' => 'Firstname',
                'last-name' => 'Lastname'
            ],
            'ip-address' => '0.0.0.1'
        ];

        $this->tx->setOperation(Operation::PAY);

        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMappedSpecificPropertiesWithoutAccountHolderThrowsException()
    {
        $this->tx->setAccountHolder(null);
        $this->tx->setOperation(Operation::PAY);
        $this->tx->mappedProperties();
    }

    public function testRetrieveTransactionForCancel()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setParentTransactionId("aa");
        $this->tx->setParentTransactionType(Transaction::TYPE_DEBIT);

        $this->assertEquals(Transaction::TYPE_REFUND_DEBIT, $this->tx->mappedProperties()['transaction-type']);
    }
}
