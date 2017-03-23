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

use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;

class IdealTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const BANK = 'ING';
    const SUCCESS_URL = 'http://www.example.com/?status=success';
    const CANCEL_URL = 'http://www.example.com/cancel';
    const DESCRIPTOR = 'dummy description';

    /**
     * @var IdealTransaction
     */
    private $tx;

    public function setUp()
    {
        $redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL);
        $this->tx = new IdealTransaction();
        $this->tx->setRedirect($redirect);
        $this->tx->setBic(self::BANK);
        $this->tx->setAmount(new Money(33, 'USD'));
        $this->tx->setDescriptor(self::DESCRIPTOR);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testSetBicThrowsUnsupportedBank()
    {
        $this->tx->setBic('asdf');
    }

    public function testMappedProperties()
    {
        $expectedResult = [
            'transaction-type' => 'debit',
            'requested-amount' => [
                'currency' => 'USD',
                'value' => '33'
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'ideal'
                    ]
                ]
            ],
            'cancel-redirect-url' => self::CANCEL_URL,
            'success-redirect-url' => self::SUCCESS_URL . '&request_id=',
            'bank-account' => ['bic' => 'INGBNL2A'],
            'descriptor' => self::DESCRIPTOR

        ];

        $this->tx->setOperation(Operation::PAY);

        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }
}
