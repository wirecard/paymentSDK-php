<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class PayPalCancel extends Base
{
    //include url of current page
    public $URL = '/PayPal/cancel.php';

    //page specific text that can be found in the URL
    public $pageSpecific = 'cancel';

    //page elements
    public $elements = array(
        'Transaction ID to be refunded' => "//*[@id='parentTransactionId']",
        'Cancel' => "//*[@class='btn btn-primary']",
        'Payment successfully cancelled.' => "Payment successfully cancelled.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a",
        'Noted Transaction Identification' => '',

    );
}
