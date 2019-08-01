<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;

class AccountHolderUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccountHolder
     */
    private $accountHolder;

    public function setUp()
    {
        $this->accountHolder = new AccountHolder();
    }

    public function testConstructor()
    {
        $xml = '<?xml version="1.0" encoding="utf-8" standalone="yes"?><payment>
          <first-name>first-name</first-name>
          <last-name>last-name</last-name>
          <email>test@test.com</email>
          <date-of-birth>12-12-2012</date-of-birth>
            <address>
                <city>city</city>
                <country>country</country>
                <street>street</street>
                <postal-code>1234</postal-code>
                <street2>street2</street2>
                <house-extension>12</house-extension>
            </address>
        </payment>';

        $accountHolder = new AccountHolder(simplexml_load_string($xml));
        $this->assertEquals('12-12-2012', $accountHolder->getDateOfBirth());
    }

    public function testGetMappedPropertiesLastAndFirstName()
    {
        $firstName = 'Jane';
        $lastName = 'Doe';
        $this->assertNotNull($this->accountHolder->setLastName($lastName)->setFirstName($firstName));

        $this->assertEquals(
            [
                'last-name' => $lastName,
                'first-name' => $firstName
            ],
            $this->accountHolder->mappedProperties()
        );
    }

    public function testGetMappedPropertiesLastNameAndEmail()
    {
        $email = 'Jane@doe.com';
        $this->assertNotNull($this->accountHolder->setEmail($email));

        $this->assertEquals(
            [
                'email' => $email
            ],
            $this->accountHolder->mappedProperties()
        );
    }

    public function testGetMappedPropertiesLastNameAndPhone()
    {
        $phone = '+123 456 789';
        $this->assertNotNull($this->accountHolder->setPhone($phone));

        $this->assertEquals(
            [
                'phone' => $phone
            ],
            $this->accountHolder->mappedProperties()
        );
    }

    public function testMappedPropertiesWithAddress()
    {
        $addr = new Address('AT', 'Graz', 'Reininghausstraße 13a');
        $addr->setPostalCode('8020');

        $this->assertNotNull($this->accountHolder->setAddress($addr));

        $expectedResult = [
            'address' => [
                'street1' => 'Reininghausstraße 13a',
                'city' => 'Graz',
                'country' => 'AT',
                'postal-code' => '8020'
            ]
        ];

        $this->assertEquals($expectedResult, $this->accountHolder->mappedProperties());
    }

    public function testGetMappedPropertiesCrmId()
    {
        $crmId = '1243df';
        $this->assertNotNull($this->accountHolder->setCrmId($crmId));

        $this->assertEquals(
            [
                'merchant-crm-id' => $crmId
            ],
            $this->accountHolder->mappedProperties()
        );
    }

    public function testGetMappedPropertiesDateOfBirth()
    {
        $dateOfBirth = new \DateTime('2016-01-01');
        $this->assertNotNull($this->accountHolder->setDateOfBirth($dateOfBirth));

        $this->assertEquals(
            [
                'date-of-birth' => $dateOfBirth->format('d-m-Y')
            ],
            $this->accountHolder->mappedProperties()
        );
    }

    public function testGetMappedPropertiesGender()
    {
        $gender = 'f';
        $this->assertNotNull($this->accountHolder->setGender($gender));

        $this->assertEquals(
            [
                'gender' => $gender
            ],
            $this->accountHolder->mappedProperties()
        );
    }

    public function testGetMappedPropertiesSecurityNumber()
    {
        $securityNumber = '1234567';
        $this->assertNotNull($this->accountHolder->setSocialSecurityNumber($securityNumber));

        $this->assertEquals(
            [
                'social-security-number' => $securityNumber
            ],
            $this->accountHolder->mappedProperties()
        );
    }

    public function testGetMappedPropertiesShippingMethod()
    {
        $shippingMethod = 'Express';
        $this->assertNotNull($this->accountHolder->setShippingMethod($shippingMethod));

        $this->assertEquals(
            [
                'shipping-method' => $shippingMethod
            ],
            $this->accountHolder->mappedProperties()
        );
    }

    public function testGetMappedSeamlessPropertiesLastNameAndEmail()
    {
        $email = 'Jane@doe.com';
        $this->accountHolder->setEmail($email);

        $this->assertEquals(
            [
                'email' => $email
            ],
            $this->accountHolder->mappedSeamlessProperties()
        );
    }

    public function testGetMappedSeamlessPropertiesLastNameAndPhone()
    {
        $phone = '+123 456 789';
        $this->accountHolder->setPhone($phone);

        $this->assertEquals(
            [
                'phone' => $phone
            ],
            $this->accountHolder->mappedSeamlessProperties()
        );
    }

    public function testGetMappedSeamlessPropertiesLastNameAndPhoneShipping()
    {
        $phone = '+123 456 789';
        $this->accountHolder->setPhone($phone);

        $this->assertEquals(
            [
                'shipping_phone' => $phone
            ],
            $this->accountHolder->mappedSeamlessProperties('shipping_')
        );
    }

    public function testGetMappedSeamlessPropertiesCrmId()
    {
        $crmId = '1243df';
        $this->accountHolder->setCrmId($crmId);

        $this->assertEquals(
            [
                'merchant_crm_id' => $crmId
            ],
            $this->accountHolder->mappedSeamlessProperties()
        );
    }

    public function testGetMappedSeamlessPropertiesDateOfBirth()
    {
        $dateOfBirth = new \DateTime('2016-01-01');
        $this->accountHolder->setDateOfBirth($dateOfBirth);

        $this->assertEquals(
            [
                'date_of_birth' => $dateOfBirth->format('d-m-Y')
            ],
            $this->accountHolder->mappedSeamlessProperties()
        );
    }

    public function testGetMappedSeamlessPropertiesGender()
    {
        $gender = 'f';
        $this->accountHolder->setGender($gender);

        $this->assertEquals(
            [
                'gender' => $gender
            ],
            $this->accountHolder->mappedSeamlessProperties()
        );
    }

    public function testGetMappedSeamlessPropertiesSecurityNumber()
    {
        $securityNumber = '1234567';
        $this->accountHolder->setSocialSecurityNumber($securityNumber);

        $this->assertEquals(
            [
                'consumer_social_security_number' => $securityNumber
            ],
            $this->accountHolder->mappedSeamlessProperties()
        );
    }

    public function testMappedSeamlessPropertiesWithAddress()
    {
        $addr = new Address('AT', 'Graz', 'Reininghausstraße 13a');
        $addr->setPostalCode('8020');

        $this->accountHolder->setAddress($addr);

        $expectedResult = [
            'street1' => 'Reininghausstraße 13a',
            'city' => 'Graz',
            'country' => 'AT',
            'postal_code' => '8020'
        ];

        $this->assertEquals($expectedResult, $this->accountHolder->mappedSeamlessProperties());
    }

    public function testMappedSeamlessPropertiesWithAddressShipping()
    {
        $addr = new Address('AT', 'Graz', 'Reininghausstraße 13a');
        $addr->setPostalCode('8020');

        $this->accountHolder->setAddress($addr);

        $expectedResult = [
            'shipping_street1' => 'Reininghausstraße 13a',
            'shipping_city' => 'Graz',
            'shipping_country' => 'AT',
            'shipping_postal_code' => '8020'
        ];

        $this->assertEquals($expectedResult, $this->accountHolder->mappedSeamlessProperties('shipping_'));
    }

    public function testGetAsHtml()
    {
        $defaults = [
            'table_id' => 'table_id',
            'table_class' => 'table_class',
            'translations' => [
                'title' => 'Account Holder',
                'first-name' => 'First Name'
            ]
        ];
        $this->accountHolder->setFirstName('firstName');
        $this->accountHolder->setLastName('lastName');
        $this->accountHolder->setPhone('123123');
        // phpcs:disable
        $expected = <<<HTML
<table id='table_id' class='table_class'><tbody><tr><td>last-name</td><td>lastName</td></tr><tr><td>First Name</td><td>firstName</td></tr><tr><td>phone</td><td>123123</td></tr></tbody></table>
HTML;
        // phpcs:enable

        $this->assertEquals(
            $expected,
            $this->accountHolder->getAsHtml($defaults)
        );
    }
}
