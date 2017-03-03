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
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;

class SepaTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const IBAN = 'DE42512308000000060004';
    const LAST_NAME = 'Doe';
    const FIRST_NAME = 'Jane';

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
        $this->tx->setIban(self::IBAN);
        $this->tx->setAccountHolder($this->accountHolder);
    }

    public function testMappedPropertiesReserveIbanOnly()
    {
        $expectedResult = $this->getExpectedResultIbanOnly();

        $result = $this->tx->mappedProperties(Operation::RESERVE, null);

        $this->assertEquals($expectedResult, $result);
    }

    public function testMappedPropertiesReserveIbanAndBic()
    {
        $bic = '42B';
        $this->tx->setBic($bic);

        $expectedResult = $this->getExpectedResultIbanOnly();
        $expectedResult['bank-account']['bic'] = $bic;

        $result = $this->tx->mappedProperties(Operation::RESERVE, null);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    private function getExpectedResultIbanOnly()
    {
        $expectedResult = [
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
        return $expectedResult;
    }
}
