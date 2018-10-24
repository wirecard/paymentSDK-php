<?php

namespace Page;

class CreditCardReservePage
{
    // include url of current page
    public static $URL = '/CreditCard/reserve.php';

    public static $elements = array(
        'Redirect to 3-D Secure page' => "//*[@class='btn btn-primary']"
    );

    public function getElement($name)
    {
        return self::$elements[$name];
    }
}
