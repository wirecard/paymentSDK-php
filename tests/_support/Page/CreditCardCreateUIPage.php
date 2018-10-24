<?php

namespace Page;

class CreditCardCreateUIPage
{
    // include url of current page
    public static $URL = '/CreditCard/createUI.php';

    public static $elements = array(
        'Last name' => "//*[@id='last_name']",
        'Card number' => "//*[@id='account_number']",
        'CVV' => "//*[@id='card_security_code']",
        'Valid until month' => "//*[@id='expiration_month_list']",
        'Valid until year' => "//*[@id='expiration_year_list']",
        'Save' => "//*[@class='btn btn-primary']",
        'Credit Card payment form' => "//*[@id='payment-form']"
    );


    //TODO refactor this method to have it in parent class
    public function getElement($name)
    {
        return self::$elements[$name];
    }
}
