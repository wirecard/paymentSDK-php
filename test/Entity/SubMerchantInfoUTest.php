<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\SubMerchantInfo;

class SubMerchantInfoUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SubMerchantInfo
     */
    private $subMerchantInfo;

    /**
     * @var SubMerchantInfo
     */
    private $subMerchantInfoExtended;

    public function setUp()
    {
        $this->subMerchantInfo = new SubMerchantInfo();
        $this->subMerchantInfoExtended = new SubMerchantInfo();
    }

    public function testGetMappedPropertiesSubMerchantInfo()
    {
        $id = '12345';
        $name = 'my name';

        $this->subMerchantInfo->setMerchantId($id);
        $this->subMerchantInfo->setMerchantName($name);

        $this->assertEquals(
            [
                'id' => $id,
                'name' => $name
            ],
            $this->subMerchantInfo->mappedProperties()
        );
    }

    public function testGetMappedPropertiesSubMerchantInfoExtended()
    {
        $id = '12345';
        $name = 'my name';
        $street = '123 test street';
        $city = 'testing town';
        $postalCode = '99999';
        $state = 'BAV';
        $country = 'DE';

        $this->subMerchantInfoExtended->setMerchantId($id);
        $this->subMerchantInfoExtended->setMerchantName($name);
        $this->subMerchantInfoExtended->setMerchantStreet($street);
        $this->subMerchantInfoExtended->setMerchantCity($city);
        $this->subMerchantInfoExtended->setMerchantPostalCode($postalCode);
        $this->subMerchantInfoExtended->setMerchantState($state);
        $this->subMerchantInfoExtended->setMerchantCountry($country);

        $this->assertEquals(
            [
                'id' => $id,
                'name' => $name,
                'street' => $street,
                'city' => $city,
                'postal-code' => $postalCode,
                'state' => $state,
                'country' => $country
            ],
            $this->subMerchantInfoExtended->mappedProperties()
        );

        $this->subMerchantInfoExtended->setMerchantState(null);
        $this->assertEquals(
            [
                'id' => $id,
                'name' => $name,
                'street' => $street,
                'city' => $city,
                'postal-code' => $postalCode,
                'country' => $country
            ],
            $this->subMerchantInfoExtended->mappedProperties()
        );
    }
}
