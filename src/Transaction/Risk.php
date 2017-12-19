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

use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\AccountHolder;

/**
 * Class Risk
 * @package Wirecard\PaymentSdk\Transaction
 *
 * Class for risk management parameter.
 */
abstract class Risk
{
    /**
     * @var AccountHolder
     */
    protected $accountHolder;

    /**
     * @var string
     */
    protected $consumerId;

    /**
     * @var string
     */
    protected $ipAddress;

    /**
     * @var string
     */
    protected $orderNumber;

    /**
     * @var string
     */
    protected $descriptor;

    /**
     * @var AccountHolder
     */
    protected $shipping;

    /**
     * @var Basket
     */
    protected $basket;

    /**
     * @var Device
     */
    protected $device;

    /**
     * @param AccountHolder $accountHolder
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @param string $consumerId
     */
    public function setConsumerId($consumerId)
    {
        $this->consumerId = $consumerId;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @param AccountHolder $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @param Basket $basket
     */
    public function setBasket($basket)
    {
        $this->basket = $basket;
    }

    /**
     * @param Device $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @param string $descriptor
     */
    public function setDescriptor($descriptor)
    {
        $this->descriptor = $descriptor;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $data = array();

        if ($this->accountHolder instanceof AccountHolder) {
            $data['account-holder'] = $this->accountHolder->mappedProperties();
        }

        if (null !== $this->ipAddress) {
            $data['ip-address'] = $this->ipAddress;
        }

        if (null !== $this->consumerId) {
            $data['consumer-id'] = $this->consumerId;
        }

        if ($this->shipping instanceof AccountHolder) {
            $data['shipping'] = $this->shipping->mappedProperties();
        }

        if ($this->basket instanceof Basket) {
            $this->basket->setVersion(self::class);
            $data['order-items'] = $this->basket->mappedProperties();
        }

        if (null !== $this->device) {
            $data['device'] = $this->device->mappedProperties();
        }

        if (null !== $this->orderNumber) {
            $data['order-number'] = $this->orderNumber;
        }

        if (null !== $this->descriptor) {
            $data['descriptor'] = $this->descriptor;
        }

        return $data;
    }
}
