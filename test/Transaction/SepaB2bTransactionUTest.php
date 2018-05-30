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

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaBtwobTransaction;

class SepaBtwobTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const IBAN = 'DE42512308000000060004';
    const MANDATE_ID = '2345';
    const COMPANY_NAME = 'Testcompany';

    /**
     * @var SepaBtwobTransaction
     */
    private $tx;

    /**
     * @var Amount
     */
    private $amount;

    public function setUp()
    {
        $this->amount = new Amount(55.5, 'EUR');

        $this->tx = new SepaBtwobTransaction();
        $this->tx->setCompanyName(self::COMPANY_NAME);
        $this->tx->setAmount($this->amount);
    }


    public function testMappedPropertiesPayIbanAndBic()
    {
        $this->tx->setIban(self::IBAN);

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
            'transaction-type' => 'debit',
            'b2b' => 'true',
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '55.5'
            ],
            'account-holder' => [
                'last-name' => self::COMPANY_NAME,
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
}
