<?php
/**
 * Created by IntelliJ IDEA.
 * User: tatjana.starcenko
 * Date: 10/23/2018
 * Time: 4:35 PM
 */

namespace Page;


class WirecardTransactionDetailsPage
{
    // include url of current page
    //TODO change URL to regural expression
    public static $URL = 'https://api-test.wirecard.com/engine/rest/merchants/';

    public static $elements = array(
        'Transaction State' => "//*[@id='mainTable']/tbody/tr[8]/td[1]",
        'SUCCESS' => "//*[@id='mainTable']/tbody/tr[8]/td[2]",
    );

    //TODO refactor this method to have it in parent class
    public function getElement($name)
    {
        return self::$elements[$name];
    }
}