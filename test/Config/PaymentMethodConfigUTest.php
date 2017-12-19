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

namespace WirecardTest\PaymentSdk\Config;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

class PaymentMethodConfigUTest extends \PHPUnit_Framework_TestCase
{
    const PAYMENT_METHOD_NAME = 'paypal';

    const MAID = '1234fdsf';

    const SECRET = 'gfdhfjfgh';

    /**
     * @var PaymentMethodConfig
     */
    private $instance;

    public function setUp()
    {
        $this->instance = new PaymentMethodConfig(self::PAYMENT_METHOD_NAME, self::MAID, self::SECRET);
    }

    public function testGetPaymentMethodName()
    {
        $this->assertEquals(self::PAYMENT_METHOD_NAME, $this->instance->getPaymentMethodName());
    }

    public function testGetMerchantAccountId()
    {
        $this->assertEquals(self::MAID, $this->instance->getMerchantAccountId());
    }

    public function testGetThreeDMerchandAccountId()
    {
        $this->assertEquals(self::MAID, $this->instance->getThreeDMerchantAccountId());
    }

    public function testGetSecret()
    {
        $this->assertEquals(self::SECRET, $this->instance->getSecret());
    }

    public function testMappedPropertiesWithoutSpecificProperties()
    {
        $this->assertEquals(
            [
                'merchant-account-id' => [
                    'value' => self::MAID
                ]
            ],
            $this->instance->mappedProperties()
        );
    }
}
