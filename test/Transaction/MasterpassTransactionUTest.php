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

use ReflectionClass;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

class MasterpassTransactionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MasterpassTransaction
     */
    private $tx;

    /**
     * @var Amount
     */
    private $amount;

    public function setUp()
    {
        $this->amount = new Amount(55.5, 'EUR');

        $this->tx = new MasterpassTransaction();
        $this->tx->setAmount($this->amount);
    }

    public function testMappedPropertiesFollowUp()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setParentTransactionId('parenttxid');

        $mappedProperties = $this->tx->mappedProperties();

        $this->assertEquals(
            CreditCardTransaction::NAME,
            $mappedProperties['payment-methods']['payment-method'][0]['name']
        );
    }

    /**
     * @expectedException Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMandatoryAccountHolderForPayReferenced()
    {
        $this->tx->setParentTransactionType(Transaction::TYPE_PURCHASE);
        $this->tx->setParentTransactionId('parentTransactionId');
        $this->tx->setOperation(Operation::PAY);
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMandatoryAccountHolderForPayNonReferenced()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setParentTransactionId(null);
        $this->tx->setParentTransactionType(null);
        $this->tx->mappedProperties();
    }

    public function testEndpointPayments()
    {
        $this->tx->setParentTransactionId('abc123');
        $this->assertEquals(Transaction::ENDPOINT_PAYMENTS, $this->tx->getEndpoint());
    }

    public function testEndpointPaymentMethods()
    {
        $this->assertEquals(Transaction::ENDPOINT_PAYMENT_METHODS, $this->tx->getEndpoint());
    }

    public function testRetrieveTransactionForCancelPurchase()
    {
        $retrieveTransactionTypeForCancel = self::getMethod('retrieveTransactionTypeForCancel');
        $this->tx->setParentTransactionType(Transaction::TYPE_PURCHASE);
        $tx = $retrieveTransactionTypeForCancel->invoke($this->tx);

        $this->assertEquals(Transaction::TYPE_REFUND_PURCHASE, $tx);
    }

    private static function getMethod($method)
    {
        $class = new ReflectionClass(MasterpassTransaction::class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    public function testRetrieveTransactionForCancelAuthorization()
    {
        $retrieveTransactionTypeForCancel = self::getMethod('retrieveTransactionTypeForCancel');
        $this->tx->setParentTransactionType(Transaction::TYPE_AUTHORIZATION);
        $tx = $retrieveTransactionTypeForCancel->invoke($this->tx);

        $this->assertEquals(Transaction::TYPE_VOID_AUTHORIZATION, $tx);
    }

    public function testRetrieveTransactionForCancelCaptureAuthorization()
    {
        $retrieveTransactionTypeForCancel = self::getMethod('retrieveTransactionTypeForCancel');
        $this->tx->setParentTransactionType(Transaction::TYPE_CAPTURE_AUTHORIZATION);
        $tx = $retrieveTransactionTypeForCancel->invoke($this->tx);

        $this->assertEquals(Transaction::TYPE_VOID_CAPTURE, $tx);
    }

    public function testRetrieveTransactionForCancelReferencedPurchase()
    {
        $retrieveTransactionTypeForCancel = self::getMethod('retrieveTransactionTypeForCancel');
        $this->tx->setParentTransactionType(Transaction::TYPE_REFERENCED_PURCHASE);
        $tx = $retrieveTransactionTypeForCancel->invoke($this->tx);

        $this->assertEquals(Transaction::TYPE_VOID_PURCHASE, $tx);
    }

    /**
     * @expectedException Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testRetrieveTransactionForCancelUnsupported()
    {
        $retrieveTransactionTypeForCancel = self::getMethod('retrieveTransactionTypeForCancel');
        $this->tx->setParentTransactionType(Transaction::TYPE_DEBIT);
        $retrieveTransactionTypeForCancel->invoke($this->tx);
    }

    public function testRetrieveTransactionForReserve()
    {
        $retrieveTransactionTypeForReserve = self::getMethod('retrieveTransactionTypeForReserve');
        $this->tx->setAmount(new Amount(1.01, 'EUR'));
        $transactionType = $retrieveTransactionTypeForReserve->invoke($this->tx);
        $this->assertEquals(Transaction::TYPE_AUTHORIZATION, $transactionType);
    }

    public function testRetrieveTransactionForReserveZeroAuth()
    {
        $retrieveTransactionTypeForReserve = self::getMethod('retrieveTransactionTypeForReserve');
        $this->tx->setAmount(new Amount(0.0, 'EUR'));
        $transactionType = $retrieveTransactionTypeForReserve->invoke($this->tx);
        $this->assertEquals(Transaction::TYPE_AUTHORIZATION_ONLY, $transactionType);
    }
}
