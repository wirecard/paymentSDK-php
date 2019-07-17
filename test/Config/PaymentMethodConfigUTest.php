<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Config;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

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

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMissingCredentials()
    {
        new PaymentMethodConfig(self::PAYMENT_METHOD_NAME);
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
