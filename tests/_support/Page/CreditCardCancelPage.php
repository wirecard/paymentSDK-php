<?php
/**
 * Created by IntelliJ IDEA.
 * User: tatjana.starcenko
 * Date: 10/25/2018
 * Time: 9:28 AM
 */

namespace Page;


class CreditCardCancelPage
{
    // include url of current page
    public static $URL = '/CreditCard/cancel.php';

    public static $elements = array(
        'Transaction ID to be refunded' => "//*[@id='parentTransactionId']",
        'Refund' => "//*[@class='btn btn-primary']",
        '"Payment successfully cancelled.' => "Payment successfully cancelled.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//*[@id='overrides']/div/a",
        'Noted Transaction Identification' => ''
    );

    public function getElement($name)
    {
        return self::$elements[$name];
    }
}