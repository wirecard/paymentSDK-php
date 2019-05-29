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

namespace WirecardTest\PaymentSdk\Mapper;

use PHPUnit_Framework_TestCase;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\Periodic;
use Wirecard\PaymentSdk\Mapper\RequestMapper;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

class RequestMapperUTest extends PHPUnit_Framework_TestCase
{
    const MAID = 'B612';

    const EXAMPLE_URL = 'http://www.example.com';

    /**
     * @var RequestMapper
     */
    private $mapper;

    protected function setUp()
    {
        $this->mapper = $this->createRequestMapper();
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testSetEmailNotification()
    {
        /** @var CreditCardConfig $config */
        $config = $this->createMock(CreditCardConfig::class);
        $config->method('getMerchantAccountId')->willReturn('B612');

        $transaction = new CreditCardTransaction();
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType(Transaction::TYPE_CREDIT);
        $transaction->setOperation(Operation::CREDIT);
        $transaction->setConfig($config);

        $transaction->setEmailNotification('email@address.com');
        $transaction->setNotificationUrl('http://www.url.com');

        $this->assertNotEmpty($transaction->mappedProperties());
    }

    public function testMappingWithPaymentMethodSpecificProperties()
    {
        $mapper = $this->createRequestMapper();

        $config = $this->createMock(CreditCardConfig::class);
        $config->method('getMerchantAccountId')->willReturn('B612');

        $followupTransaction = new CreditCardTransaction();
        $followupTransaction->setParentTransactionId('642');
        $followupTransaction->setParentTransactionType(Transaction::TYPE_CREDIT);
        $followupTransaction->setOperation(Operation::CREDIT);
        $followupTransaction->setConfig($config);
        $followupTransaction->setLocale('de');


        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';
        $result = $mapper->map($followupTransaction);

        $expectedResult = ['payment' => [
            'request-id' => '5B-dummy-id',
            'merchant-account-id' => ['value' => 'B612'],
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'ip-address' => '0.0.0.1',
            'parent-transaction-id' => '642',
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'transaction-type' => 'credit'
        ]];
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResult), $result);
    }

    /**
     * @return \Closure
     */
    private function createRequestIdGeneratorMock()
    {
        return function () {
            return '5B-dummy-id';
        };
    }

    /**
     * @return RequestMapper
     */
    private function createRequestMapper()
    {
        $dummyPaymentMethodConfig = new PaymentMethodConfig('dummy', self::MAID, 'secret');
        $config = $this->createMock(Config::class);
        $config->method('get')->willReturn($dummyPaymentMethodConfig);
        /**
         * @var Config $config
         */
        $requestIdGeneratorMock = $this->createRequestIdGeneratorMock();
        return new RequestMapper($config, $requestIdGeneratorMock);
    }

    public function testMappingSeamlessWithPaymentMethodSpecificProperties()
    {
        $mapper = $this->createRequestMapper();

        $config = $this->createMock(CreditCardConfig::class);
        $config->method('getMerchantAccountId')->willReturn('B612');

        $transaction = new CreditCardTransaction();
        $transaction->setConfig($config);
        $transaction->setAmount(new Amount(10, 'EUR'));
        $transaction->setAccountHolder(new AccountHolder());
        $transaction->setShipping(new AccountHolder());
        $transaction->setBasket(new Basket());
        $transaction->setCustomFields(new CustomFieldCollection());
        $transaction->setNotificationUrl(self::EXAMPLE_URL);
        $transaction->setDescriptor('Test1');
        $transaction->setOrderNumber('123');
        $transaction->setIpAddress('127.0.0.1');
        $transaction->setConsumerId('cons123');
        $transaction->setDevice(new Device());
        $transaction->setPeriodic(new Periodic('ci', 'first'));
        $requestdata = ['transaction-type' => 'authorization'];

        $result = $mapper->mapSeamlessRequest($transaction, $requestdata);

        $expectedResult = [
            'transaction-type' => 'authorization',
            'notification_transaction_url' => self::EXAMPLE_URL,
            'notifications_format' => 'application/xml',
            'descriptor' => 'Test1',
            'order_number' => '123',
            'ip_address' => '127.0.0.1',
            'consumer_id' => 'cons123',
            'device_fingerprint' => null,
            'periodic_type' => 'ci',
            'sequence_type' => 'first'
        ];

        $this->assertEquals($expectedResult, $result);
    }
}
