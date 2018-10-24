<?php

namespace Page;

class CreditCardSuccessPage
{
    // include url of current page
    public static $URL = '/CreditCard/return.php?status=success';

    public static $elements = array(
        'Payment successfully completed' => "Payment successfully completed.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//*[@id='overrides']/div/a"

    );

    public function getElement($name)
    {
        return self::$elements[$name];
    }
}
