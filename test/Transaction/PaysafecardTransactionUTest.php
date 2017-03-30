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

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PaysafecardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class PaysafecardTransactionUTest extends \PHPUnit_Framework_TestCase
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
            ]
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
