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
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PaysafecardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class PaysafecardTransactionUTest extends PHPUnit_Framework_TestCase
{
    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';

    /**
     * @var PaysafecardTransaction
     */
    private $tx;

    public function setUp()
    {
        $redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL);
        $this->tx = new PaysafecardTransaction();
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount(new Amount(33, 'USD'));
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
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
                        'name' => 'paysafecard'
                    ]
                ]
            ],
            'cancel-redirect-url' => self::CANCEL_URL,
            'success-redirect-url' => self::SUCCESS_URL,
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
            'ip-address' => '0.0.0.1'
        ];

        $this->tx->setOperation(Operation::PAY);

        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testMappedPropertiesMinimum()
    {
        $tx = new PaysafecardTransaction();
        $expectedResult = [
            'transaction-type' => 'authorization',
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'paysafecard'
                    ]
                ]
            ],
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'ip-address' => '0.0.0.1'
        ];

        $tx->setOperation(Operation::RESERVE);

        $result = $tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function endpointDataProvider()
    {
        return [
            [Operation::RESERVE, PaysafecardTransaction::ENDPOINT_PAYMENT_METHODS],
            [Operation::PAY, PaysafecardTransaction::ENDPOINT_PAYMENT_METHODS],
            [Operation::CANCEL, PaysafecardTransaction::ENDPOINT_PAYMENTS],
        ];
    }

    /**
     * @param $operation
     * @param $expected
     * @dataProvider endpointDataProvider
     */
    public function testGetEndpoint($operation, $expected)
    {
        $this->tx->setOperation($operation);
        $this->assertEquals($expected, $this->tx->getEndpoint());
    }

    public function testGetEndpointWithParentTransactionIdAndPay()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setParentTransactionId('1435');
        $this->assertEquals(PaysafecardTransaction::ENDPOINT_PAYMENTS, $this->tx->getEndpoint());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testCancelWithoutParentIdThrowsException()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testCancelWithInvalidParentTransactionTypeThrowsException()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setParentTransactionId('1');
        $this->tx->mappedProperties();
    }

    public function testCancel()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setParentTransactionId('1');
        $this->tx->setParentTransactionType(PaysafecardTransaction::TYPE_AUTHORIZATION);
        $data = $this->tx->mappedProperties();

        $this->assertEquals('void-authorization', $data['transaction-type']);
    }

    public function testCapture()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setParentTransactionType(PaysafecardTransaction::TYPE_AUTHORIZATION);
        $data = $this->tx->mappedProperties();

        $this->assertEquals('capture-authorization', $data['transaction-type']);
    }
}
