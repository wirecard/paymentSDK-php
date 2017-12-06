<?php
/**
 * Shop System Payment SDK - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
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
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class RatepayTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class RatepayTransaction extends Transaction implements Reservable
{
    const NAME = 'ratepay-install';

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var Device
     */
    protected $device;

    /**
     * @var Basket
     */
    protected $basket;

    /**
     * @param string $orderNumber
     * @return RatepayTransaction
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    /**
     * @param string $device
     * @return Transaction
     */
    public function setDevice($device)
    {
        $this->device = $device;
        return $this;
    }

    /**
     * @param Basket $basket
     * @return Transaction
     */
    public function setBasket(Basket $basket)
    {
        $this->basket = $basket;
        return $this;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $result = [
            'order-number' => $this->orderNumber,
        ];

        if (null !== $this->device) {
            $result['device'] = $this->device->mappedProperties();
        }

        if ($this->basket instanceof Basket) {
            $this->basket->setVersion(self::class);
            $result['order-items'] = $this->basket->mappedProperties();
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        return self::TYPE_AUTHORIZATION;
    }

    /**
     * @throws MandatoryFieldMissingException
     * @return mixed
     */
    protected function retrieveTransactionTypeForPay()
    {
        if ($this->parentTransactionId) {
            return self::TYPE_CAPTURE_AUTHORIZATION;
        }

        throw new MandatoryFieldMissingException('Parent transaction id is missing for pay operation.');
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
        if ($this->parentTransactionType === self::TYPE_AUTHORIZATION) {
            return self::TYPE_VOID_AUTHORIZATION;
        } elseif ($this->parentTransactionType === self::TYPE_CAPTURE_AUTHORIZATION) {
            return self::TYPE_REFUND_CAPTURE;
        }

        throw new UnsupportedOperationException('The transaction can not be canceled.');
    }


    /**
     * @return string
     */
    protected function retrieveTransactionTypeForCredit()
    {
        return self::TYPE_CREDIT;
    }

    /**
     * return string
     */
    public function getEndpoint()
    {
        if ($this->operation === Operation::RESERVE) {
            return self::ENDPOINT_PAYMENT_METHODS;
        }

        return self::ENDPOINT_PAYMENTS;
    }
}
