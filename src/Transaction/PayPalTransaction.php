<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class PayPalTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class PayPalTransaction extends Transaction implements Reservable
{
    const NAME = 'paypal';

    /**
     * Maximum characters: 27
     */
    const DESCRIPTOR_LENGTH = 27;

    /**
     * Allowed characters:
     * umlaut, - '0-9','a-z','A-Z',' ' , '+',',','-','.'
     */
    const DESCRIPTOR_ALLOWED_CHAR_REGEX = "/[^a-zA-Z0-9\s\'\+\,\-\.\Ä\Ö\Ü\ä\ö\ü]/u";

    /**
     * @var string
     */
    private $orderDetail;

    /**
     * @param string $orderDetail
     * @return PayPalTransaction
     */
    public function setOrderDetail($orderDetail)
    {
        $this->orderDetail = $orderDetail;
        return $this;
    }

    /**
     * return string
     */
    public function getEndpoint()
    {
        if (null !== $this->parentTransactionId && $this->operation !== Operation::RESERVE) {
            return self::ENDPOINT_PAYMENTS;
        }

        return self::ENDPOINT_PAYMENT_METHODS;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $transactionType = $this->retrieveTransactionType();
        $data = array();

        if ($transactionType === self::TYPE_AUTHORIZATION_ONLY) {
            $data['periodic']['periodic-type'] = 'recurring';
            $data['periodic']['sequence-type'] = 'first';
        }

        if (null !== $this->shipping) {
            $data['shipping'] = $this->shipping->mappedProperties();
        }

        if (null !== $this->orderNumber) {
            $data['order-number'] = $this->orderNumber;
        }

        if (null !== $this->orderDetail) {
            $data['order-detail'] = $this->orderDetail;
        }

        if (null !== $this->descriptor) {
            $data['descriptor'] = $this->descriptor;
        }

        if ($this->basket instanceof Basket) {
            $this->basket->setVersion(self::class);
            $data['order-items'] = $this->basket->mappedProperties();
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        if ($this->amount->getValue() === 0.0) {
            return self::TYPE_AUTHORIZATION_ONLY;
        }

        return self::TYPE_AUTHORIZATION;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        if ($this->parentTransactionType) {
            switch ($this->parentTransactionType) {
                case self::TYPE_AUTHORIZATION:
                    return self::TYPE_CAPTURE_AUTHORIZATION;
                case self::TYPE_DEBIT:
                    return self::TYPE_DEBIT;
                default:
                    throw new UnsupportedOperationException('The transaction cannot be captured');
            }
        }

        return self::TYPE_DEBIT;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }
        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
                $transactionType = self::TYPE_VOID_AUTHORIZATION;
                break;
            case self::TYPE_DEBIT:
                $transactionType = self::TYPE_REFUND_DEBIT;
                break;
            case self::TYPE_CAPTURE_AUTHORIZATION:
                $transactionType = self::TYPE_REFUND_CAPTURE;
                break;
            default:
                throw new UnsupportedOperationException('The transaction can not be canceled.');
        }

        return $transactionType;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForCredit()
    {
        return self::TYPE_PENDING_CREDIT;
    }
}
