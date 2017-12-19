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
 * Class Address
 * @package Wirecard\PaymentSdk\Entity
 *
 * An entity representing a physical address.
 */
class Address implements MappableEntity
{
    /**
     * @var string
     *
     * The 2-character code of the country.
     */
    private $countryCode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $street1;

    /**
     * @var string
     */
    private $street2;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $houseExtension;

    /**
     * Address constructor.
     * @param string $countryCode
     * @param string $city
     * @param string $street1
     */
    public function __construct($countryCode, $city, $street1)
    {
        $this->countryCode = $countryCode;
        $this->city = $city;
        $this->street1 = $street1;
    }

    /**
     * @param string $street2
     * Enter the house number incl. suffixes here.
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @param string $houseExtension
     */
    public function setHouseExtension($houseExtension)
    {
        $this->houseExtension = $houseExtension;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $result = [
            'street1' => $this->street1,
            'city' => $this->city,
            'country' => $this->countryCode
        ];

        if (null !== $this->postalCode) {
            $result['postal-code'] = $this->postalCode;
        }

        if (null !== $this->street2) {
            $result['street2'] = $this->street2;
        } else {
            if (strlen($this->street1) > 128) {
                $result['street1'] = substr($this->street1, 0, 128);
                $result['street2'] = substr($this->street1, 128);
            }
        }

        if (null !== $this->houseExtension) {
            $result['house-extension'] = $this->houseExtension;
        }

        return $result;
    }
}
