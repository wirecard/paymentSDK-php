<?php

/* Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class WirecardTransactionDetails extends Base
{
    public $page_specific = '/engine/rest/merchants/';

    public function seeTransactionType($tableKey, $tableValue)
    {
        $I = $this->tester;
        $details = $I->grabMultiple("//*[@id='mainTable']/tbody/tr/td");
        for ($i = 0; $i < count($details); $i = $i + 2) {
            if ($details[$i] == $tableKey && $details[$i+1] == $tableValue) {
                $I->see($details[$i]);
                $I->see($details[$i+1]);
            }
        }
    }
}