<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity\Payload;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Constant\PayloadFields;
use Wirecard\PaymentSdk\Entity\Payload\NvpPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\PayloadDataFactory;
use Wirecard\PaymentSdk\Entity\Payload\PayPalPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\RatepayPayloadData;
use Wirecard\PaymentSdk\Entity\Payload\SyncPayloadData;

class PayloadDataFactoryUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    public function setUp()
    {
        $this->config = new Config('baseUrl', 'user', 'password');
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate($payload, $expectedPayloadDataClass)
    {
        $payloadDataFactory = new PayloadDataFactory($payload, $this->config);
        $this->assertInstanceOf($expectedPayloadDataClass, $payloadDataFactory->create());
    }

    public function createDataProvider()
    {
        return [
            [
                [
                    PayloadFields::FIELD_SYNC_RESPONSE => 'asdasdasdasdasdadasdasd'
                ],
                SyncPayloadData::class
            ], [
                [
                    PayloadFields::FIELD_BASE64_PAYLOAD => 'asdasdasdasdasdadasdasd',
                    PayloadFields::FIELD_PSP_NAME => 'psp'
                ],
                RatepayPayloadData::class
            ], [
                [
                    PayloadFields::FIELD_EPP_RESPONSE => 'asdasdasdasdasdadasdasd'
                ],
                PayPalPayloadData::class
            ], [
                [
                    PayloadFields::FIELD_RESPONSE_SIGNATURE => 'asdasdasdasdasdadasdasd'
                ],
                NvpPayloadData::class
            ],
        ];
    }
}
