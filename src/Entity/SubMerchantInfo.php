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

namespace Wirecard\PaymentSdk\Entity;

/**
 * Class Device
 * @package Wirecard\PaymentSdk\Entity
 *
 * An immutable entity representing a SubMerchant.
 *
 * @since 2.3.0
 */
class SubMerchantInfo implements MappableEntity
{
    const TYPE_COMMON = 1;
    const TYPE_EXTENDED = 2;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $merchantName;

    /**
     * @var string
     */
    private $merchantStreet;

    /**
     * @var string
     */
    private $merchantCity;

    /**
     * @var string
     */
    private $merchantPostalCode;

    /**
     * @var string
     */
    private $merchantState;

    /**
     * @var string
     */
    private $merchantCountry;

    public function __construct($type = self::TYPE_COMMON)
    {
        $this->type = $type;
    }

    /**
     * @param $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param $merchantName
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
    }

    /**
     * @param $merchantStreet
     */
    public function setMerchantStreet($merchantStreet)
    {
        $this->merchantStreet = $merchantStreet;
    }

    /**
     * @param $merchantCity
     */
    public function setMerchantCity($merchantCity)
    {
        $this->merchantCity = $merchantCity;
    }

    /**
     * @param $merchantPostalCode
     */
    public function setMerchantPostalCode($merchantPostalCode)
    {
        $this->merchantPostalCode = $merchantPostalCode;
    }

    /**
     * @param $merchantState
     */
    public function setMerchantState($merchantState)
    {
        $this->merchantState = $merchantState;
    }

    /**
     * @param $merchantCountry
     */
    public function setMerchantCountry($merchantCountry)
    {
        $this->merchantCountry = $merchantCountry;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $result = array();

        if (null !== $this->merchantId) {
            $result['id'] = $this->merchantId;
        }

        if (null !== $this->merchantName) {
            $result['name'] = $this->merchantName;
        }

        if ($this->type == self::TYPE_EXTENDED) {
            if (null !== $this->merchantStreet) {
                $result['street'] = $this->merchantStreet;
            }

            if (null !== $this->merchantCity) {
                $result['city'] = $this->merchantCity;
            }

            if (null !== $this->merchantPostalCode) {
                $result['postal-code'] = $this->merchantPostalCode;
            }

            if (null !== $this->merchantState) {
                $result['state'] = $this->merchantState;
            }

            if (null !== $this->merchantCountry) {
                $result['country'] = $this->merchantCountry;
            }
        }

        return $result;
    }
}
