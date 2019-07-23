<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\SubMerchantInfo;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class WeChatTransaction
 * @package Wirecard\PaymentSdk\Transaction
 * @since 2.3.0
 */
class WeChatTransaction extends Transaction implements Reservable
{
    const NAME = 'wechat-qrpay';

    /**
     * @var SubMerchantInfo
     */
    protected $subMerchantInfo;

    /**
     * @var string
     */
    private $orderDetail;

    /**
     * @param $orderDetail
     */
    public function setOrderDetail($orderDetail)
    {
        $this->orderDetail = $orderDetail;
    }

    /**
     * @param SubMerchantInfo $subMerchantInfo
     */
    public function setSubMerchantInfo($subMerchantInfo)
    {
        $this->subMerchantInfo = $subMerchantInfo;
    }

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $result = array();

        if (null !== $this->subMerchantInfo) {
            $result['sub-merchant-info'] = $this->subMerchantInfo->mappedProperties();
        }

        if (null !== $this->orderDetail) {
            $result['order-detail'] = $this->orderDetail;
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        if (null === $this->subMerchantInfo) {
            throw new MandatoryFieldMissingException('Sub merchant info not set.');
        }

        if (null === $this->orderDetail) {
            throw new MandatoryFieldMissingException('Order detail not set.');
        }

        return self::TYPE_DEBIT;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        if ($this->parentTransactionType != Transaction::TYPE_DEBIT) {
            throw new UnsupportedOperationException('Only debit can be refunded.');
        }

        return Transaction::TYPE_VOID_DEBIT;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForRefund()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        if ($this->parentTransactionType != Transaction::TYPE_DEBIT) {
            throw new UnsupportedOperationException('Only debit can be refunded.');
        }

        return Transaction::TYPE_REFUND_DEBIT;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        if ($this->operation == Operation::CANCEL ||
            $this->operation == Operation::REFUND) {
            return self::ENDPOINT_PAYMENTS;
        }
        return self::ENDPOINT_PAYMENT_METHODS;
    }
}
