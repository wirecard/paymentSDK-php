<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class PayPalCancel extends Base
{

    public $URL = '/PayPal/cancel.php';

    public $page_specific = 'cancel';

    public $elements = array(
        'Transaction ID to be refunded' => "//*[@id='parentTransactionId']",
        'Cancel' => "//*[@class='btn btn-primary']",
        'Payment successfully cancelled.' => "Payment successfully cancelled.",
        'Transaction ID' => "Transaction ID",
        'Transaction Identification' => "//div[contains(@class, 'content')]/a",
        'Noted Transaction Identification' => '',

    );
}