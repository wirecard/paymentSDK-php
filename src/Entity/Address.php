<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Wirecard\IsoToPayPal\Converter;
use Wirecard\IsoToPayPal\Exception\CountryNotFoundException;
use Wirecard\IsoToPayPal\Exception\StateNotFoundException;

/**
 * Class Address
 * @package Wirecard\PaymentSdk\Entity
 *
 * An entity representing a physical address.
 */
class Address implements MappableEntity
{
    /**
     * @var Converter
     *
     * The ISO 3166-2 to PayPal converter.
     */
    private $converter;

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
    private $street3;

    /**
     * @var string
     */
    private $state;

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
        $this->converter = new Converter();
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
     * @param $street3
     * Enter the house number incl. suffixes here.
     * @since 3.8.0
     */
    public function setStreet3($street3)
    {
        $this->street3 = $street3;
    }

    /**
     * @param string $state
     * @since 3.0.1
     * Set the state variable
     */
    public function setState($state)
    {
        // If we fail, we just set an unfiltered state, because it can be assumed it is not relevant for our use case.
        try {
            $stateCode = $this->converter->convert($this->countryCode, trim($state));
            $this->state = $stateCode;
        } catch (StateNotFoundException $e) {
            $this->state = $state;
        } catch (CountryNotFoundException $e) {
            $this->state = $state;
        }
    }

    /**
     * @return string
     * @since 3.0.1
     */
    public function getState()
    {
        return $this->state;
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
            'city' => $this->city,
            'country' => $this->countryCode,
        ];

        if (!is_null($this->state)) {
            $result['state'] = $this->state;
        }

        if (!is_null($this->postalCode)) {
            $result['postal-code'] = $this->postalCode;
        }

        $result = array_merge(
            $result,
            $this->truncatePropertyIfSet('street1'),
            $this->truncatePropertyIfSet('street2'),
            $this->truncatePropertyIfSet('street3')
        );

        if (!is_null($this->houseExtension)) {
            $result['house-extension'] = $this->houseExtension;
        }

        return $result;
    }

    /**
     * @param string $type
     * @return array
     */
    public function mappedSeamlessProperties($type = '')
    {
        $result = [
            $type . 'city' => $this->city,
            $type . 'country' => $this->countryCode
        ];

        if (!is_null($this->postalCode)) {
            $result[$type . 'postal_code'] = $this->postalCode;
        }

        $result = array_merge(
            $result,
            $this->truncatePropertyIfSet('street1', $type),
            $this->truncatePropertyIfSet('street2', $type),
            $this->truncatePropertyIfSet('street3', $type)
        );

        return $result;
    }

    /**
     * @param $property
     * @param string $prefix
     * @param int $start
     * @param int $length
     * @return array
     * @since 3.9.0 Do not truncate empty properties
     * @since 3.8.0
     */
    private function truncatePropertyIfSet($property, $prefix = '', $start = 0, $length = 128)
    {
        $data = array();

        if (isset($this->{$property}) && !empty($this->{$property})) {
            $data[$prefix . $property] = mb_substr($this->{$property}, $start, $length);
        }

        return $data;
    }
}
