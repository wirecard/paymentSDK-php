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
use Wirecard\PaymentSdk\Entity\SubMerchantInfo;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Transaction\WeChatTransaction;

class WeChatTransactionUTest extends PHPUnit_Framework_TestCase
{
    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';
    const FAILURE_URL = 'http://www.example.com/failure';

    /**
     * @var WeChatTransaction
     */
    private $tx;

    /**
     * @var SubMerchantInfo
     */
    private $subMerchantInfo;

    public function setUp()
    {
        $redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL, self::FAILURE_URL);

        $this->tx = new WeChatTransaction();
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount(new Amount(10, 'USD'));
        $this->tx->setLocale('de');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '0.0.0.1';

        $this->subMerchantInfo = new SubMerchantInfo();
        $this->subMerchantInfo->setMerchantId('12345');
        $this->subMerchantInfo->setMerchantName('my store');
    }

    public function testGetEndpointPayments()
    {
        $this->tx->setOperation(Operation::CANCEL);

        $this->assertEquals(Transaction::ENDPOINT_PAYMENTS, $this->tx->getEndpoint());
    }

    public function testGetEndpointPaymentmethods()
    {
        $this->tx->setOperation(Operation::PAY);

        $this->assertEquals(Transaction::ENDPOINT_PAYMENT_METHODS, $this->tx->getEndpoint());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMandatoryParentTxForCancel()
    {
        $this->tx->setOperation(Operation::CANCEL);

        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testParentTransactionTypeForCancel()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setParentTransactionId('123');
        $this->tx->setParentTransactionType(Transaction::TYPE_PURCHASE);

        $this->tx->mappedProperties();
    }

    public function testMappedProperties()
    {
        $expectedResult = [
            'transaction-type' => Transaction::TYPE_DEBIT,
            'requested-amount' => [
                'currency' => 'USD',
                'value' => '10'
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'wechat-qrpay'
                    ]
                ]
            ],
            'sub-merchant-info' => [
                'id' => '12345',
                'name' => 'my store'
            ],
            'order-detail' => 'details',
            'cancel-redirect-url' => self::CANCEL_URL,
            'success-redirect-url' => self::SUCCESS_URL,
            'fail-redirect-url' => self::FAILURE_URL,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'ip-address' => '0.0.0.1'
        ];

        $this->tx->setOperation(Operation::PAY);
        $this->tx->setSubMerchantInfo($this->subMerchantInfo);
        $this->tx->setOrderDetail('details');

        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMandatorySubMerchantInfoForPay()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setOrderDetail('detail');
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMandatoryOrderDetailForPay()
    {
        $this->tx->setOperation(Operation::PAY);
        $this->tx->setSubMerchantInfo($this->subMerchantInfo);
        $this->tx->mappedProperties();
    }

    public function testRetrieveTransactionForCancel()
    {
        $this->tx->setOperation(Operation::CANCEL);
        $this->tx->setParentTransactionId("aa");
        $this->tx->setParentTransactionType(Transaction::TYPE_DEBIT);
        $this->assertEquals(Transaction::TYPE_VOID_DEBIT, $this->tx->mappedProperties()['transaction-type']);
    }

    public function testRetrieveTransactionForRefund()
    {
        $this->tx->setOperation(Operation::REFUND);
        $this->tx->setParentTransactionId("aa");
        $this->tx->setParentTransactionType(Transaction::TYPE_DEBIT);
        $this->assertEquals(Transaction::TYPE_REFUND_DEBIT, $this->tx->mappedProperties()['transaction-type']);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMandatoryParentTxForRefund()
    {
        $this->tx->setOperation(Operation::REFUND);

        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testParentTransactionTypeForRefund()
    {
        $this->tx->setOperation(Operation::REFUND);
        $this->tx->setParentTransactionId('123');
        $this->tx->setParentTransactionType(Transaction::TYPE_PURCHASE);

        $this->tx->mappedProperties();
    }
}
