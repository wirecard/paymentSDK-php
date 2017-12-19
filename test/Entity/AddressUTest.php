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

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Address;

class AddressUTest extends \PHPUnit_Framework_TestCase
{
    const AT_COUNTRY_CODE = 'AT';
    const GRAZ = 'Graz';
    const DUMMY_ADDRESS = 'Reininghausstraße 13a';

    /**
     * @var Address
     */
    private $addr;

    protected function setUp()
    {
        $this->addr = new Address(self::AT_COUNTRY_CODE, self::GRAZ, self::DUMMY_ADDRESS);
    }

    public function testMappingOnlyRequiredFields()
    {
        $expectedResult = [
            'street1' => self::DUMMY_ADDRESS,
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedProperties());
    }

    public function testMappingWithPostalCode()
    {
        $this->addr->setPostalCode('8020');

        $expectedResult = [
            'street1' => self::DUMMY_ADDRESS,
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'postal-code' => '8020'
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedProperties());
    }

    public function testMappingWithStreet2()
    {
        $this->addr->setStreet2('1st floor');

        $expectedResult = [
            'street1' => self::DUMMY_ADDRESS,
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'street2' => '1st floor'
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedProperties());
    }

    public function testMappingWithVeryLongStreet1()
    {
        // @codingStandardsIgnoreStart
        $street1 = 'This is a long street name in order to test an improbable but possible input. And to verify that it is split into the two fieldsWith this sentence the 2nd part starts.';
        // @codingStandardsIgnoreEnd
        $this->addr = new Address(self::AT_COUNTRY_CODE, self::GRAZ, $street1);

        $expectedResult = [
            // @codingStandardsIgnoreStart
            'street1' => 'This is a long street name in order to test an improbable but possible input. And to verify that it is split into the two fields',
            // @codingStandardsIgnoreEnd
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'street2' => 'With this sentence the 2nd part starts.'
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedProperties());
    }

    public function testMappingWithVeryLongStreet1WithStreet2()
    {
        // @codingStandardsIgnoreStart
        $street1 = 'This is a long street name in order to test an improbable but possible input. And to verify that it is not split, if street2 is also given.';
        // @codingStandardsIgnoreEnd
        $this->addr = new Address(self::AT_COUNTRY_CODE, self::GRAZ, $street1);
        $this->addr->setStreet2('some suffix');

        $expectedResult = [
            'street1' =>
            // @codingStandardsIgnoreStart
                'This is a long street name in order to test an improbable but possible input. And to verify that it is not split, if street2 is also given.',
            // @codingStandardsIgnoreEnd
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'street2' => 'some suffix'
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedProperties());
    }

    public function testMappingWithHouseExtension()
    {
        $this->addr->setHouseExtension('123b');

        $expectedResult = [
            'street1' => self::DUMMY_ADDRESS,
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'house-extension' => '123b'
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedProperties());
    }
}
