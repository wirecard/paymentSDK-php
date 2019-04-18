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
     * @since 3.7.0
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
            'street1' => $this->street1,
            'city' => $this->city,
            'country' => $this->countryCode,
        ];

        if (!is_null($this->state)) {
            $result['state'] = $this->state;
        }

        if (!is_null($this->postalCode)) {
            $result['postal-code'] = $this->postalCode;
        }

        $result = $this->mapAdditionalStreets($result);

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
            $type . 'street1' => $this->street1,
            $type . 'city' => $this->city,
            $type . 'country' => $this->countryCode
        ];

        if (!is_null($this->postalCode)) {
            $result[$type . 'postal_code'] = $this->postalCode;
        }

        $result = $this->mapAdditionalStreets($result, $type);

        return $result;
    }

    /**
     * @param array $result
     * @param string $type
     * @return array
     * @since 3.7.0
     */
    private function mapAdditionalStreets($result, $type = '')
    {
        if (isset($this->street2) || isset($this->street3)) {
            $result[$type . 'street1'] = mb_substr($this->street1, 0, 50);
            $result = array_merge(
                $result,
                $this->truncatePropertyIfSet('street2'),
                $this->truncatePropertyIfSet('street3')
            );

            return $result;
        }

        if (mb_strlen($this->street1) <= 50) {
            return $result;
        }

        $explodedStreet = $this->wordWrappedExplodeByLength($this->street1);
        $prefix = $type . 'street';
        for ($i=0; $i < count($explodedStreet) && $i < 3; $i++) {
            if ($i > 0) {
                $prefix = 'street';
            }
            $counter = $i + 1;
            $result[$prefix . $counter] = $explodedStreet[$i];
        }

        return $result;
    }

    /**
     * @param $property
     * @param int $start
     * @param int $length
     * @return array
     * @since 3.7.0
     */
    private function truncatePropertyIfSet($property, $start = 0, $length = 50)
    {
        $data = array();

        if (isset($this->{$property})) {
            $data[$property] = mb_substr($this->{$property}, $start, $length);
        }

        return $data;
    }

    /**
     * @param $string
     * @param int $length
     * @return array
     * @since 3.7.0
     */
    private function wordWrappedExplodeByLength($string, $length = 50)
    {
        $data = array();

        if (preg_match_all("/.{1,{$length}}(?=\W+)/", $string, $lines) !== false) {
            for ($i=0; $i < count($lines[0]); $i++) {
                $data[$i] = trim($lines[0][$i]);
            }
        }

        return $data;
    }
}
