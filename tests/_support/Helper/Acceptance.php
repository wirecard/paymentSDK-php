<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    public static function formAuthLink($link, $username, $password)
    {
        $link_parts = parse_url($link);
        $link_parts["user"] = $username;
        $link_parts["pass"] = $password;

        $new_link = $link_parts['scheme'] . '://' . $link_parts["user"] . ":" . $link_parts["pass"] . "@" . $link_parts['host'] . $link_parts['path'];
        return $new_link;
    }

    public static function getTransactionIDFromLink($link)
    {
        $transaction_id = explode('/', $link);
        return $transaction_id = end($transaction_id);
    }
}
