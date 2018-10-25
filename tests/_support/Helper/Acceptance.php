<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    public static function formAuthLink($link, $username, $password)
    {
        $credentials = $username . ":" . $password . "@";
        //insert username and password into link address
        $link_address_start = substr($link, 0, stripos($link, "/") + 2);
        $link_address_end = substr($link, stripos($link, "/") + 2);
        return $link_address = $link_address_start . $credentials . $link_address_end;
    }

    public static function getTransactionIDFromLink($link)
    {
        $transaction_id = explode('/', $link);
        return $transaction_id = end($transaction_id);
    }
}
