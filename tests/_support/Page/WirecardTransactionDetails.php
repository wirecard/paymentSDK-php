<?php

/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class WirecardTransactionDetails extends Base
{
    public $page_specific = '/engine/rest/merchants/';
    public $elements = array(
        'authorization' => "//*[@id='mainTable']/tbody/tr[7]/td[2]",
    );
}
