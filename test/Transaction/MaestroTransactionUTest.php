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
use Wirecard\PaymentSdk\Config\MaestroConfig;
use Wirecard\PaymentSdk\Transaction\MaestroTransaction;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Operation;

class MaestroTransactionUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MaestroTransaction
     */
    private $tx;

    /**
     * @var MaestroConfig
     */
    private $config;

    public function setUp()
    {
        $this->config = new MaestroConfig('maid', 'secret');
        $this->tx = new MaestroTransaction();
        $this->tx->setConfig($this->config);
    }

    public function testSslCreditCardTransactionWithTokenId()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'transaction-type' => 'check-enrollment',
            'card-token' => [
                'token-id' => '21'
            ],
            'ip-address' => '127.0.0.1',
            'merchant-account-id' => [
                'value' => null
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
            'periodic' => ['periodic-type' => 'recurring']
        ];

        $transaction = new MaestroTransaction();
        $transaction->setConfig($this->config);
        $transaction->setTokenId('21');
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setOperation(Operation::RESERVE);

        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }
}
