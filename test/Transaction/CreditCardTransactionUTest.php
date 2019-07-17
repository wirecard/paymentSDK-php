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
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Card;
use Wirecard\PaymentSdk\Entity\Periodic;
use Wirecard\PaymentSdk\Entity\SubMerchantInfo;
use Wirecard\PaymentSdk\Exception\UnsupportedEncodingException;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

class CreditCardTransactionUTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CreditCardTransaction
     */
    private $tx;

    /**
     * @var CreditCardConfig
     */
    private $config;

    public function setUp()
    {
        $this->config = new CreditCardConfig('maid', 'secret');
        $this->tx = new CreditCardTransaction();
        $this->tx->setConfig($this->config);
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testSetTermUrl()
    {
        $this->tx->setTermUrl('test');
        $this->assertAttributeEquals('test', 'termUrl', $this->tx);
    }

    public function testGetTermUrl()
    {
        $this->tx->setTermUrl('test');
        $this->assertEquals('test', $this->tx->getTermUrl());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setTokenId('anything');

        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMapPropertiesNoTokenIdNoParentTransactionId()
    {
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->mappedProperties();
    }

    public function testSslCreditCardTransactionWithTokenId()
    {
        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'transaction-type' => 'authorization',
            'card-token' => [
                'token-id' => '21'
            ],
            'ip-address' => '127.0.0.1',
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
            'periodic' => ['periodic-type' => 'recurring']
        ];

        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setTokenId('21');
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setOperation(Operation::RESERVE);

        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testSslCreditCardTransactionWithTokenIdAndSubMerchantInfo()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $id = '12345';
        $name = 'my name';
        $street = '123 test street';
        $city = 'testing town';
        $postalCode = '99999';
        $state = 'BAV';
        $country = 'DE';

        $subMerchantInfo = new SubMerchantInfo();
        $subMerchantInfo->setMerchantId($id);
        $subMerchantInfo->setMerchantName($name);
        $subMerchantInfo->setMerchantStreet($street);
        $subMerchantInfo->setMerchantCity($city);
        $subMerchantInfo->setMerchantPostalCode($postalCode);
        $subMerchantInfo->setMerchantState($state);
        $subMerchantInfo->setMerchantCountry($country);

        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setTokenId('45');
        $transaction->setAmount(new Amount(12.34, 'EUR'));
        $transaction->setOperation(Operation::RESERVE);
        $transaction->setSubMerchantInfo($subMerchantInfo);

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 12.34],
            'transaction-type' => 'authorization',
            'card-token' => [
                'token-id' => '45'
            ],
            'ip-address' => '127.0.0.1',
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
            'periodic' => ['periodic-type' => 'recurring'],
            'sub-merchant-info' => [
                'id' => $id,
                'name' => $name,
                'street' => $street,
                'city' => $city,
                'postal-code' => $postalCode,
                'state' => $state,
                'country' => $country
            ],
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testMappedPropertiesPares()
    {
        $this->tx->setPaRes('pasdsgf');
        $valid = [
            'payment-methods' => [
                'payment-method' => [
                    [
                        'name' => 'creditcard'
                    ]
                ]
            ],
            'transaction-type' => 'testtype',
            'three-d' => [
                'pares' => 'pasdsgf'
            ],
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
            'ip-address' => '127.0.0.1'
        ];
        $this->tx->setOperation('testtype');
        $this->assertEquals($valid, $this->tx->mappedProperties());
    }

    public function testSslCreditCardTransactionWithParentTransactionId()
    {

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent5',
            'transaction-type' => 'referenced-authorization',
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
            'ip-address' => '127.0.0.1'
        ];

        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setParentTransactionId('parent5');
        $transaction->setParentTransactionType(Transaction::TYPE_AUTHORIZATION);
        $transaction->setOperation(Operation::RESERVE);
        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testSslCreditCardTransactionWithoutTokenIdAndParentTransactionId()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setOperation(Operation::RESERVE);
        $transaction->mappedProperties();
    }

    public function testSslCreditCardTransactionWithBothTokenIdAndParentTransactionId()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent5',
            'ip-address' => '127.0.0.1',
            'transaction-type' => 'referenced-authorization',
            'card-token' => [
                'token-id' => '33'
            ],
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];

        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setTokenId('33');
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setParentTransactionId('parent5');
        $transaction->setParentTransactionType(Transaction::TYPE_AUTHORIZATION);
        $transaction->setOperation(Operation::RESERVE);
        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function testCancelProvider()
    {
        return [
            [
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_VOID_AUTHORIZATION
            ],
            [
                Transaction::TYPE_REFERENCED_AUTHORIZATION,
                Transaction::TYPE_VOID_AUTHORIZATION
            ],
            [
                'refund-capture',
                'void-refund-capture'
            ],
            [
                'refund-purchase',
                'void-refund-purchase'
            ],
            [
                Transaction::TYPE_CREDIT,
                'void-credit'
            ],
            [
                CreditCardTransaction::TYPE_PURCHASE,
                'void-purchase'
            ],
            [
                CreditCardTransaction::TYPE_REFERENCED_PURCHASE,
                'void-purchase'
            ],
            [
                Transaction::TYPE_CAPTURE_AUTHORIZATION,
                'void-capture'
            ]
        ];
    }

    /**
     * @dataProvider testCancelProvider
     * @param $transactionType
     * @param $cancelType
     */
    public function testCancel($transactionType, $cancelType)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType($transactionType);
        $transaction->setOperation(Operation::CANCEL);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => '127.0.0.1',
            'transaction-type' => $cancelType,
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testPayProvider()
    {
        return [
            [
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_CAPTURE_AUTHORIZATION
            ],
            [
                CreditCardTransaction::TYPE_PURCHASE,
                CreditCardTransaction::TYPE_REFERENCED_PURCHASE
            ],
            [
                CreditCardTransaction::TYPE_CHECK_ENROLLMENT,
                CreditCardTransaction::TYPE_PURCHASE
            ],
            [
                null,
                CreditCardTransaction::TYPE_PURCHASE
            ]
        ];
    }

    /**
     * @dataProvider testPayProvider
     * @param $transactionType
     * @param $payType
     */
    public function testPay($transactionType, $payType)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType($transactionType);
        $transaction->setOperation(Operation::PAY);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1,0.0.0.1';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => '127.0.0.1',
            'transaction-type' => $payType,
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testCancelNoParentId()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setOperation(Operation::CANCEL);
        $transaction->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testCancelInvalidParentTransaction()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType('test');
        $transaction->setLocale('de');
        $transaction->setOperation(Operation::CANCEL);
        $transaction->mappedProperties();
    }

    /**
     * @return array
     */
    public function testRefundProvider()
    {
        return [
            [
                CreditCardTransaction::TYPE_PURCHASE,
                'refund-purchase'
            ],
            [
                CreditCardTransaction::TYPE_REFERENCED_PURCHASE,
                'refund-purchase'
            ],
            [
                Transaction::TYPE_CAPTURE_AUTHORIZATION,
                'refund-capture'
            ]
        ];
    }

    /**
     * @dataProvider testRefundProvider
     * @param $transactionType
     * @param $refundType
     */
    public function testRefund($transactionType, $refundType)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType($transactionType);
        $transaction->setOperation(Operation::REFUND);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $result = $transaction->mappedProperties();
        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => '127.0.0.1',
            'transaction-type' => $refundType,
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testRefundNoParentId()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setOperation(Operation::REFUND);
        $transaction->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testRefundInvalidParentTransaction()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType('test');
        $transaction->setOperation(Operation::REFUND);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $transaction->mappedProperties();
    }

    public function testCredit()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType(Transaction::TYPE_CREDIT);
        $transaction->setOperation(Operation::CREDIT);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => '127.0.0.1',
            'transaction-type' => 'credit',
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testRetrieveOperationTypeAuthorization()
    {
        $tx = new CreditCardTransaction();
        $tx->setConfig($this->config);
        $tx->setOperation(Operation::RESERVE);

        $this->assertEquals(Transaction::TYPE_AUTHORIZATION, $tx->retrieveOperationType());
    }

    public function testRetrieveOperationTypePurchase()
    {
        $tx = new CreditCardTransaction();
        $tx->setConfig($this->config);
        $tx->setOperation(Operation::PAY);

        $this->assertEquals(CreditCardTransaction::TYPE_PURCHASE, $tx->retrieveOperationType());
    }

    public function threeDProvider()
    {
        return [
            [
                Operation::CANCEL,
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_VOID_AUTHORIZATION
            ],
            [
                Operation::RESERVE,
                null,
                CreditCardTransaction::TYPE_CHECK_ENROLLMENT
            ],
            [
                Operation::RESERVE,
                CreditCardTransaction::TYPE_CHECK_ENROLLMENT,
                Transaction::TYPE_AUTHORIZATION
            ],
            [
                Operation::RESERVE,
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_REFERENCED_AUTHORIZATION
            ],
            [
                Operation::PAY,
                null,
                CreditCardTransaction::TYPE_CHECK_ENROLLMENT
            ],
        ];
    }
    /**
     * @param $operation
     * @param $parentTransactionType
     * @param $expectedType
     * @dataProvider threeDProvider
     */
    public function testThreeDCreditCardTransaction($operation, $parentTransactionType, $expectedType)
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent54',
            'ip-address' => '192.168.1.1',
            'transaction-type' => $expectedType,
            'card-token' => [
                'token-id' => '21'
            ],
            'merchant-account-id' => [
                'value' => 'maid'
            ],
            'locale' => 'de',
            'entry-mode' => 'telephone',
            'card' => [
                'card-type' => 'card type',
                'expiration-month' => 'expiration month',
                'expiration-year' => 'expiration year'
            ],
            'order-id' => 'orderid123',
            'periodic' => ['periodic-type' => 'ci']
        ];

        $this->config->addSslMaxLimit(new Amount(20.0, 'EUR'));
        $this->config->setThreeDCredentials('maid', '123abcd');
        $amount = new Amount(24, 'EUR');
        $transaction = new CreditCardTransaction();
        $transaction->setConfig($this->config);
        $transaction->setTokenId('21');
        $transaction->setTermUrl('https://example.com/r');
        $transaction->setAmount($amount);
        $transaction->setParentTransactionId('parent54');
        $transaction->setParentTransactionType($parentTransactionType);
        $transaction->setOperation($operation);
        $transaction->setEntryMode('telephone');
        $transaction->setLocale('de');
        $transaction->setOrderId('orderid123');
        $transaction->setPeriodic(new Periodic('ci'));

        $card = new Card();
        $card->setExpirationMonth('expiration month');
        $card->setExpirationYear('expiration year');
        $card->setType('card type');

        $transaction->setCard($card);

        $result = $transaction->mappedProperties();
        $this->assertEquals($expectedResult, $result);
    }

    public function testIsThreeDWithSetThreeD()
    {
        $this->tx->setThreeD(false);
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setTokenId('21');

        $result = $this->tx->mappedProperties();

        $this->assertEquals(CreditCardTransaction::TYPE_PURCHASE, $result['transaction-type']);
    }

    public function testIsThreeDWithThreeDMinLimit()
    {
        $this->config->addThreeDMinLimit(new Amount(20.0, 'EUR'));
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setTokenId('21');
        $this->tx->setAmount(new Amount(20.1, 'EUR'));

        $result = $this->tx->mappedProperties();

        $this->assertEquals(CreditCardTransaction::TYPE_CHECK_ENROLLMENT, $result['transaction-type']);
    }

    /**
     * @return array
     */
    public function isFallbackProvider()
    {
        return [
            [false, null, null, null],
            [false, new Amount(70.0, 'EUR'), null, null],
            [
                true,
                new Amount(70.0, 'EUR'),
                null,
                new Amount(50.0, 'EUR')
            ],
            [
                true,
                new Amount(70.0, 'EUR'),
                new Amount(100.0, 'EUR'),
                new Amount(50.0, 'EUR')
            ]
        ];
    }

    /**
     * @dataProvider isFallbackProvider
     * @param $expected
     * @param $amount
     * @param $sslMaxLimit
     * @param $threeDMinLimit
     */
    public function testIsFallback($expected, $amount, $sslMaxLimit, $threeDMinLimit)
    {
        if (null !== $amount) {
            $this->tx->setAmount($amount);
        }

        if (null !== $sslMaxLimit) {
            $this->config->addSslMaxLimit($sslMaxLimit);
        }

        if (null !== $threeDMinLimit) {
            $this->config->addThreeDMinLimit($threeDMinLimit);
        }

        $this->assertEquals($expected, $this->tx->isFallback());
    }

    public function testGetEndpoint()
    {
        $this->assertEquals(Transaction::ENDPOINT_PAYMENTS, $this->tx->getEndpoint());
    }

    public function testDescriptor()
    {
        $transaction = new CreditCardTransaction();
        $transaction->setDescriptor('Test üöäü?=(&$§"§$!');
        $this->assertEquals('Test', $transaction->getDescriptor());
    }
}
