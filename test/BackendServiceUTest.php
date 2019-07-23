<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk;

use Psr\Log\LoggerInterface;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Mockery as m;

/**
 * Class TransactionServiceUTest
 * @package WirecardTest\PaymentSdk
 */
class BackendServiceUTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BackendService $service
     */
    private $service;

    public function setUp()
    {
        $config = new Config('https://api-test.wirecard.com', 'user', 'password');
        $ccardConfig = new CreditCardConfig('maid', 'secret');
        $ccardConfig->setThreeDCredentials('3dmaid', '3dsecret');
        $ccardConfig->addSslMaxLimit(new Amount(100, 'EUR'));
        $ccardConfig->addThreeDMinLimit(new Amount(50, 'EUR'));
        $config->add($ccardConfig);
        $this->service = new BackendService($config);
    }

    public function testRetrieveBackendOperations()
    {
        $service = m::mock(BackendService::class);
        $service->shouldReceive('retrieveBackendOperations')->andReturn(false);
        $transaction = new CreditCardTransaction();
        $transaction->setParentTransactionId('id');

        $this->assertFalse($service->retrieveBackendOperations($transaction));
    }

    public function testGetOrderState()
    {
        $testTransactionType = [
            Transaction::TYPE_AUTHORIZATION => BackendService::TYPE_AUTHORIZED,
            Transaction::TYPE_VOID_PURCHASE => BackendService::TYPE_REFUNDED,
            Transaction::TYPE_VOID_AUTHORIZATION => BackendService::TYPE_CANCELLED,
            Transaction::TYPE_REFUND_DEBIT => BackendService::TYPE_REFUNDED,
            Transaction::TYPE_VOID_CAPTURE => BackendService::TYPE_REFUNDED,
            Transaction::TYPE_DEBIT => BackendService::TYPE_PROCESSING,
            Transaction::TYPE_DEPOSIT => BackendService::TYPE_PROCESSING,
            Transaction::TYPE_CAPTURE_AUTHORIZATION => BackendService::TYPE_PROCESSING
        ];

        foreach ($testTransactionType as $type => $expected) {
            $this->assertEquals($expected, $this->service->getOrderState($type));
        }
    }

    public function testIsFinal()
    {
        $testFinal = [
            Transaction::TYPE_AUTHORIZATION => false,
            Transaction::TYPE_VOID_PURCHASE => true,
            Transaction::TYPE_VOID_AUTHORIZATION => true,
            Transaction::TYPE_REFUND_DEBIT => true,
            Transaction::TYPE_VOID_CAPTURE => true,
            Transaction::TYPE_DEBIT => false,
            Transaction::TYPE_DEPOSIT => true,
            Transaction::TYPE_CAPTURE_AUTHORIZATION => false
        ];

        foreach ($testFinal as $type => $expected) {
            $this->assertEquals($expected, $this->service->isFinal($type));
        }
    }
}
