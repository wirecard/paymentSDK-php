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

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

class AmountUTest extends \PHPUnit_Framework_TestCase
{
    const VALUE = 42.21;
    const EUR = 'EUR';

    /**
     * @var Amount
     */
    private $amount;

    public function setUp()
    {
        $this->amount = new Amount(self::VALUE, self::EUR);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::VALUE, $this->amount->getValue());
    }

    public function testGetCurrency()
    {
        $this->assertEquals(self::EUR, $this->amount->getCurrency());
    }

    public function constructorDataProvider()
    {
        return [
            ['151515.515151,6612456', 151515515151.6612456],
            ['151515,515151.665613', 151515515151.665613],
            ['2135345.1234365', 2135345.1234365],
            ['1234235,21435', 1234235.21435],
            [1, 1.0],
            [1.235485, 1.235485]
        ];
    }

    /**
     * @dataProvider constructorDataProvider
     * @param $value
     * @param $expected
     */
    public function testConstructor($value, $expected)
    {
        $amount = new Amount($value, 'EUR');

        $this->assertEquals($expected, $amount->getValue());
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testConstructorThrowsException()
    {
        new Amount('asdfsg124345.235,65.34523436fdg', 'EUR');
    }
}
