<?php
/**
 * Shop System Payment SDK - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
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
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Transaction;

use UnexpectedValueException;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\ApplePayTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

class ApplePayTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const SUCCESS_URL = 'http://www.example.com/success';

    /**
     * @var ApplePayTransaction
     */
    private $tx;

    public function setUp()
    {
        $redirect = new Redirect(self::SUCCESS_URL);
        $this->tx = new ApplePayTransaction();
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount(new Amount(33, 'USD'));
    }

    public function testGetConfigKey()
    {
        $this->assertEquals(ApplePayTransaction::PAYMENT_NAME, $this->tx->getConfigKey());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testSetCryptogramError()
    {
        $this->tx->setCryptogram('wrong cryptogram');
    }

    public function testRetrieveTransactionTypeForReserve()
    {
        $this->assertEquals(Transaction::TYPE_AUTHORIZATION, $this->tx->retrieveTransactionTypeForReserve());
    }

    public function testSetCryptogramSuccess()
    {
        $cryptogram = base64_encode('test cryptogram');

        $this->tx->setCryptogram($cryptogram);
        $this->tx->setOperation(Operation::RESERVE);

        $expectedResult = [
            'transaction-type' => Transaction::TYPE_AUTHORIZATION,
            'requested-amount' => [
                'currency' => 'USD',
                'value' => 33.0
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'creditcard'
                    ]
                ]
            ],
            'success-redirect-url' => self::SUCCESS_URL,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'cryptogram' => [
                'cryptogram-type' => 'apple-pay',
                'cryptogram-value' => $cryptogram
            ]
        ];

        $this->assertEquals($expectedResult, $this->tx->mappedProperties());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMappedPropertiesError()
    {
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->mappedProperties();
    }
}
