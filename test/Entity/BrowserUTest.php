<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Browser;

class BrowserUTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT'] = 'first';
        $_SERVER['HTTP_USER_AGENT'] = 'second';
    }

    public function testConstructWithParams()
    {
        $browser = new Browser('first', 'second');
        $expected = ['accept' => 'first', 'user-agent' => 'second'];

        $this->assertEquals($expected, $browser->mappedProperties());
    }

    public function testConstructorWithoutParams()
    {
        $browser = new Browser();
        $expected = ['accept' => $_SERVER['HTTP_ACCEPT'], 'user-agent' => $_SERVER['HTTP_USER_AGENT']];

        $this->assertEquals($expected, $browser->mappedProperties());
    }

    public function testSetAccept()
    {
        $browser = new Browser();
        $browser->setAccept('first');

        $expected = ['accept' => 'first', 'user-agent' => $_SERVER['HTTP_USER_AGENT']];

        $this->assertEquals($expected, $browser->mappedProperties());
    }

    public function setTestUserAgent()
    {
        $browser = new Browser();
        $browser->setUserAgent('second');

        $expected = ['accept' => $_SERVER['HTTP_ACCEPT'], 'user-agent' => $_SERVER['HTTP_USER_AGENT']];

        $this->assertEquals($expected, $browser->mappedProperties());
    }
}
