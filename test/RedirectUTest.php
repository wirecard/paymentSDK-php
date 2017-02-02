<?php

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\Redirect;

class RedirectUTest extends \PHPUnit_Framework_TestCase
{
    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';
    /**
     * @var Redirect
     */
    private $redirect;

    public function setUp()
    {
        $this->redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL);
    }

    public function testGetSuccessUrl()
    {
        $this->assertEquals(self::SUCCESS_URL, $this->redirect->getSuccessUrl());
    }

    public function testGetCancelUrl()
    {
        $this->assertEquals(self::CANCEL_URL, $this->redirect->getCancelUrl());
    }
}
