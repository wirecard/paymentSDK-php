<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk;

use Wirecard\PaymentSdk\TransactionService;
use Mockery as m;

/**
 * Class TransactionServiceITest
 * @package WirecardTest\PaymentSdk
 */
class TransactionServiceITest extends \PHPUnit_Framework_TestCase
{
    public function testCheckCredentials()
    {
        $service = m::mock(TransactionService::class);
        $service->shouldReceive('checkCredentials')->andReturn(true)->once();

        $this->assertTrue($service->checkCredentials());
    }
}
