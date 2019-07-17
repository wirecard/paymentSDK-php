<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Redirect;

class RedirectUTest extends \PHPUnit_Framework_TestCase
{
    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';
    const FAILURE_URL = 'http://www.example.com/redirect';
    /**
     * @var Redirect
     */
    private $redirect;

    public function setUp()
    {
        $this->redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL, self::FAILURE_URL);
    }

    public function testGetSuccessUrl()
    {
        $this->assertEquals(self::SUCCESS_URL, $this->redirect->getSuccessUrl());
    }

    public function testGetCancelUrl()
    {
        $this->assertEquals(self::CANCEL_URL, $this->redirect->getCancelUrl());
    }

    public function testGetFailureUrl()
    {
        $this->assertEquals(self::FAILURE_URL, $this->redirect->getFailureUrl());
    }
}
