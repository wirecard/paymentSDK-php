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
use Wirecard\PaymentSdk\Transaction\EpsTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;

class EpsTransactionUTest extends PHPUnit_Framework_TestCase
{
    public function testMappedProperties()
    {
        $tx = new EpsTransaction();
        $tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
        $amount = new Amount(18.4, 'EUR');
        $tx->setAmount($amount);

        $redirect = new Redirect(
            'http://www.test.at/return.php?status=success',
            null,
            'http://www.test.at/return.php?status=failure'
        );
        $tx->setRedirect($redirect);

        $tx->setOperation(Operation::PAY);

        $expectedResult = [
            'transaction-type' => 'debit',
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '18.4'
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'eps'
                    ]
                ]
            ],
            'success-redirect-url' => 'http://www.test.at/return.php?status=success',
            'fail-redirect-url' => 'http://www.test.at/return.php?status=failure',
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'ip-address' => '0.0.0.1'
        ];

        $result = $tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testDescriptor()
    {
        $descriptor = "0123-€\$§%!=#~;+/?:().,'&><\"*{}[]@\_°^ |ÄÖÜäöüß°`abcdefghijklmn" .
            "opqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $expectedDescriptor = "0123-€\$§%!=#~;+/?:().,'&><\"*{}[]@\_°^ |ÄÖÜäöüß°" .
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $transaction = new EpsTransaction();
        $transaction->setDescriptor($descriptor);
        $this->assertEquals($expectedDescriptor, $transaction->getDescriptor());
    }
}
