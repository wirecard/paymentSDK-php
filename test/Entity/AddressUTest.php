<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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

    public function testMappingWithState()
    {

        $address = new Address('US', 'Portland', '1 Main Street');
        $address->setState('OR');

        $this->assertEquals('OR', $address->getState());

        $expectedResult = [
            'street1' => '1 Main Street',
            'city' => 'Portland',
            'country' => 'US',
            'state' => 'OR'
        ];

        $this->assertEquals($expectedResult, $address->mappedProperties());
    }

    public function testMappingWithStateNotFoundException()
    {

        $address = new Address('US', 'Portland', '1 Main Street');
        $address->setState('ZZZ');

        $this->assertEquals('ZZZ', $address->getState());

        $expectedResult = [
            'street1' => '1 Main Street',
            'city' => 'Portland',
            'country' => 'US',
            'state' => 'ZZZ'
        ];

        $this->assertEquals($expectedResult, $address->mappedProperties());
    }

    public function testMappingWithCountryNotFoundException()
    {

        $address = new Address('AT', 'Portland', '1 Main Street');
        $address->setState('ZZZ');

        $this->assertEquals('ZZZ', $address->getState());

        $expectedResult = [
            'street1' => '1 Main Street',
            'city' => 'Portland',
            'country' => 'AT',
            'state' => 'ZZZ'
        ];

        $this->assertEquals($expectedResult, $address->mappedProperties());
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
                'This is a long street name in order to test an improbable but possible input. And to verify that it is not split, if street2 is ',
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

    public function testMappingOnlyRequiredFieldsSeamless()
    {
        $expectedResult = [
            'street1' => self::DUMMY_ADDRESS,
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedSeamlessProperties());
    }

    public function testMappingWithPostalCodeSeamless()
    {
        $this->addr->setPostalCode('8020');

        $expectedResult = [
            'street1' => self::DUMMY_ADDRESS,
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'postal_code' => '8020'
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedSeamlessProperties());
    }

    public function testMappingWithStreet2Seamless()
    {
        $this->addr->setStreet2('1st floor');

        $expectedResult = [
            'street1' => self::DUMMY_ADDRESS,
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'street2' => '1st floor'
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedSeamlessProperties());
    }

    public function testMappingWithVeryLongStreet1Seamless()
    {
        // @codingStandardsIgnoreStart
        $street1 = 'This is a long street name in order to test an improbable but possible inputs. And to verify that it is split into the two fieldsWith this sentence the 2nd part starts.';
        // @codingStandardsIgnoreEnd
        $this->addr = new Address(self::AT_COUNTRY_CODE, self::GRAZ, $street1);
        $this->addr->setStreet2('some suffix');
        $this->addr->setStreet3('another suffix');

        $expectedResult = [
            // @codingStandardsIgnoreStart
            'street1' => 'This is a long street name in order to test an improbable but possible inputs. And to verify that it is split into the two field',
            // @codingStandardsIgnoreEnd
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'street2' => 'some suffix',
            'street3' => 'another suffix',
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedSeamlessProperties());
    }

    public function testMappingWithVeryLongStreet1WithStreet2Seamless()
    {
        // @codingStandardsIgnoreStart
        $street1 = 'This is a long street name in order to test an improbable but possible input. And to verify that it is not split, if street2 is also given.';
        // @codingStandardsIgnoreEnd
        $this->addr = new Address(self::AT_COUNTRY_CODE, self::GRAZ, $street1);
        $this->addr->setStreet2('some suffix');

        $expectedResult = [
            'street1' =>
            // @codingStandardsIgnoreStart
                'This is a long street name in order to test an improbable but possible input. And to verify that it is not split, if street2 is ',
            // @codingStandardsIgnoreEnd
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'street2' => 'some suffix'
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedSeamlessProperties());
    }

    public function testMappingWithMultibyteStreet1()
    {
        // @codingStandardsIgnoreStart
        $street1 = "Die deutsche Sprache enthält neben äöü und dessen Großbuchstaben ÄÖÜ auch das ß. русский язык, Θεσσαλονίκη and eastern europe chars like Dž, Đ, Ž or Š may be a problem to";
        // @codingStandardsIgnoreEnd
        $this->addr = new Address(self::AT_COUNTRY_CODE, self::GRAZ, $street1);

        $expectedResult = [
            // @codingStandardsIgnoreStart
            'street1' => 'Die deutsche Sprache enthält neben äöü und dessen Großbuchstaben ÄÖÜ auch das ß. русский язык, Θεσσαλονίκη and eastern europe ch',
            // @codingStandardsIgnoreEnd
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedSeamlessProperties());
    }

    public function testMappingWithMultibyteOneWordStreet1()
    {
        // @codingStandardsIgnoreStart
        $street1 = "DiedeutscheSpracheenthältnebenäöüunddessenGroßbuchstabenÄÖÜauchdasß.русскийязык,ΘεσσαλονίκηandeasterneuropecharslikeDž,Đ,ŽorŠmaybeaproblemto";
        // @codingStandardsIgnoreEnd
        $this->addr = new Address(self::AT_COUNTRY_CODE, self::GRAZ, $street1);
        $this->addr->setStreet2('stabenÄÖÜauchdasß.русскийязык,Θεσσαλονίκηandeaste');
        $this->addr->setStreet3('neuropecharslikeDž,Đ,ŽorŠmaybeaproblemto');

        $expectedResult = [
            // @codingStandardsIgnoreStart
            'street1' => 'DiedeutscheSpracheenthältnebenäöüunddessenGroßbuchstabenÄÖÜauchdasß.русскийязык,ΘεσσαλονίκηandeasterneuropecharslikeDž,Đ,ŽorŠmay',
            // @codingStandardsIgnoreEnd
            'city' => self::GRAZ,
            'country' => self::AT_COUNTRY_CODE,
            'street2' => 'stabenÄÖÜauchdasß.русскийязык,Θεσσαλονίκηandeaste',
            'street3' => 'neuropecharslikeDž,Đ,ŽorŠmaybeaproblemto',
        ];

        $this->assertEquals($expectedResult, $this->addr->mappedSeamlessProperties());
    }
}
