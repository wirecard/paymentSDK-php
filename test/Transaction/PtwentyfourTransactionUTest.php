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
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\PtwentyfourTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

class PtwentyfourTransactionUTest extends PHPUnit_Framework_TestCase
{
    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';
    const FAILURE_URL = 'http://www.example.com/failure';

    /**
     * @var PtwentyfourTransaction
     */
    private $tx;

    public function setUp()
    {
        $redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL, self::FAILURE_URL);
        $this->tx = new PtwentyfourTransaction();
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount(new Amount(33, 'PLN'));
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

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMandatoryAccountHolder()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }

    public function testMappedProperties()
    {
        $expectedResult = [
            'transaction-type' => Transaction::TYPE_DEBIT,
            'requested-amount' => [
                'currency' => 'PLN',
                'value' => '33'
            ],
            'account-holder' => [

            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'p24'
                    ]
                ]
            ],
            'cancel-redirect-url' => self::CANCEL_URL,
            'success-redirect-url' => self::SUCCESS_URL,
            'fail-redirect-url' => self::FAILURE_URL,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'ip-address' => '0.0.0.1'
        ];

        $this->tx->setOperation(Operation::PAY);
        $this->tx->setAccountHolder(new AccountHolder());

        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testRetrieveTransactionForCancel()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setParentTransactionId("aa");
        $this->tx->setParentTransactionType(Transaction::TYPE_DEBIT);
        $this->assertEquals(Transaction::TYPE_REFUND_REQUEST, $this->tx->mappedProperties()['transaction-type']);
    }

    public function testDescriptor()
    {
        $descriptor = "0123-€\$§%!=#~;+/?:().,'&><\"*{}[]@\_°^|ÄÖÜäöüß^°`" .
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        // Only 20 chars are allowed
        $expectedDescriptor = "0123abcdefghijklmnop";
        $transaction = new PtwentyfourTransaction();
        $transaction->setDescriptor($descriptor);
        $this->assertEquals($expectedDescriptor, $transaction->getDescriptor());
    }
}
