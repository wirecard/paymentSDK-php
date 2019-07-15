<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Config;

use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;

class SepaConfigUTest extends \PHPUnit_Framework_TestCase
{

    public function testMappedProperties()
    {
        $accountId = 'accountId';
        $conf = new SepaConfig(SepaCreditTransferTransaction::NAME, $accountId, 'secret');
        $creditorId = '555-cred-id';
        $conf->setCreditorId($creditorId);

        $expectedResult = [
            'merchant-account-id' => [
                'value' => $accountId
            ],
            'creditor-id' => $creditorId
        ];

        $result = $conf->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }
}
