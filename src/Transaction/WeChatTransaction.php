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

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\SubMerchantInfo;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

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
