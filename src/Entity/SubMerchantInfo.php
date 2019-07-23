<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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

        return $result;
    }
}
