<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Config;

use Wirecard\PaymentSdk\Transaction\MaestroTransaction;

class MaestroConfig extends CreditCardConfig
{
    /**
     * MaestroConfig constructor.
     * @param string|null $merchantAccountId
     * @param string|null $secret
     */
    public function __construct($merchantAccountId = null, $secret = null)
    {
        parent::__construct($merchantAccountId, $secret, MaestroTransaction::NAME);
    }
}
