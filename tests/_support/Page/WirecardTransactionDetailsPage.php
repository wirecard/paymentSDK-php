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
    //TODO add regular expression to link
    public static $URL = 'https://api-test.wirecard.com/engine/rest/merchants/';
    //TODO add methods of finding transaction state in the table and getting its value
    //$rows = $driver->findElements(WebDriverBy::cssSelector('table tr'));
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