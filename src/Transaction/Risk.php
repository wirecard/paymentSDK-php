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
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Exception\UnsupportedEncodingException;

/**
 * Class Risk
 * @package Wirecard\PaymentSdk\Transaction
 *
 * Class for risk management parameter.
 */
abstract class Risk
{

    const DESCRIPTOR_LENGTH = 64;
    const DESCRIPTOR_ALLOWED_CHAR_REGEX = "//";
    const DESCRIPTOR_CHARSET = "UTF-8";

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
     * @return AccountHolder
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @param AccountHolder $accountHolder
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @return string
     */
    public function getConsumerId()
    {
        return $this->consumerId;
    }

    /**
     * @param string $consumerId
     */
    public function setConsumerId($consumerId)
    {
        $this->consumerId = $consumerId;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        if (isset($this->ipAddress)) {
            return $this->ipAddress;
        } else {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
                if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                    return $ips[0];
                } else {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
            }
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return AccountHolder
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param AccountHolder $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return Basket
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * @param Basket $basket
     */
    public function setBasket($basket)
    {
        $this->basket = $basket;
    }

    /**
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param Device $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * @param string $descriptor
     */
    public function setDescriptor($descriptor)
    {
        if (!mb_detect_encoding($descriptor, self::DESCRIPTOR_CHARSET, true)) {
            throw new UnsupportedEncodingException('Unsupported character encoding in descriptor');
        }
        $this->descriptor = $this->sanitizeDescriptor(
            $descriptor,
            static::DESCRIPTOR_LENGTH,
            static::DESCRIPTOR_ALLOWED_CHAR_REGEX
        );
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

        $data['ip-address'] = $this->getIpAddress();

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

    /**
     * The function removes not allowed characters from string via regex and limits the string to max allowed length
     * @param string $descriptor
     * @param int $length
     * @param string $regex
     * @return string
     * @since 3.6.6
     */
    private function sanitizeDescriptor($descriptor, $length, $regex)
    {
        $sanitizedDescriptor =  preg_replace($regex, '', $descriptor);
        //Remove double spaces and cut to width
        return mb_strimwidth(preg_replace('/\s+/', ' ', $sanitizedDescriptor), 0, $length);
    }
}
