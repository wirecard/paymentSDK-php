<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCancel extends Base
{
    // include url of current page
    public $URL = '/CreditCard/cancel.php';

    public $page_specific = 'cancel';

    public $elements = array(
        'Transaction ID to be refunded' => "//*[@id='parentTransactionId']",
        'Refund' => "//*[@class='btn btn-primary']",
        '"Payment successfully cancelled.' => "Payment successfully cancelled.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//*[@id='overrides']/div/a",
        'Noted Transaction Identification' => '',
        'Amount' => "//*[@id='amount']",
        'Currency' => "//*[@id='currency']",
    );
}
