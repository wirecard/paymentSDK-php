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
        $this->subMerchantInfoExtended = new SubMerchantInfo(SubMerchantInfo::TYPE_EXTENDED);
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
