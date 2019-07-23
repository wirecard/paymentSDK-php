<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Amount;

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
    public function testNonNumericValue()
    {
        $amount = new Amount('10.00', 'EUR');
    }
}
