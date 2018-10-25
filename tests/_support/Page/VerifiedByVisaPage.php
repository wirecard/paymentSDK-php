<?php

namespace Page;

class VerifiedByVisaPage
{
    // include url of current page
    //TODO change URL to regural expression
    public static $URL = 'https://c3-test.wirecard.com/acssim/app/bank';

    public static $elements = array(
        'Password' => "//*[@id='password']",
        'Continue' => "//*[@name='authenticate']"
    );

    public function getElement($name)
    {
        return self::$elements[$name];
    }
}
