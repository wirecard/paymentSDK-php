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
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaBtwobTransaction;

class SepaBtwobTransactionUTest extends PHPUnit_Framework_TestCase
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
        $this->tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
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
            'ip-address' => '0.0.0.1'
        ];
    }
}
