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

use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class PayPalTransactionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PayPalTransaction
     */
    private $tx;

    public function setUp()
    {
        $this->tx = new PayPalTransaction();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }

    public function testGetRetrieveTransactionTypeReserve()
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('getCancelUrl')->willReturn('cancel-url');
        $redirect->method('getSuccessUrl')->willReturn('success-url');
        
        $this->tx->setRedirect($redirect);
        $this->tx->setOperation('reserve');
        $data = $this->tx->mappedProperties();

        $this->assertEquals(Transaction::TYPE_AUTHORIZATION, $data['transaction-type']);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testGetRetrieveTransactionTypeCancelWithoutParent()
    {
        $this->tx->setOperation('cancel');
        $this->tx->mappedProperties();
    }

    public function testGetRetrieveTransactionTypeCancelAuthorization()
    {
        $this->tx->setParentTransactionType(Transaction::TYPE_AUTHORIZATION);
        $this->tx->setOperation('cancel');
        $this->tx->mappedProperties();

        $data = $this->tx->mappedProperties();

        $this->assertEquals(Transaction::TYPE_VOID_AUTHORIZATION, $data['transaction-type']);
    }

    public function testGetRetrieveTransactionTypeCancelDebit()
    {
        $this->tx->setParentTransactionType(Transaction::TYPE_DEBIT);
        $this->tx->setOperation('cancel');
        $this->tx->mappedProperties();

        $data = $this->tx->mappedProperties();

        $this->assertEquals('refund-debit', $data['transaction-type']);
    }

    public function testGetEndpointWithParent()
    {
        $this->tx->setParentTransactionId('gfghfgh');
        $this->assertEquals(Transaction::ENDPOINT_PAYMENTS, $this->tx->getEndpoint());
    }

    public function testGetEndpoint()
    {
        $this->assertEquals(Transaction::ENDPOINT_PAYMENT_METHODS, $this->tx->getEndpoint());
    }
}
