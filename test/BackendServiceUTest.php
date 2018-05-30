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
        $transaction = new CreditCardTransaction();
        $transaction->setParentTransactionId('id');

        $this->assertFalse($this->service->retrieveBackendOperations($transaction));
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
