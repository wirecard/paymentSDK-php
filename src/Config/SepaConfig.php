<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Config;

class SepaConfig extends PaymentMethodConfig
{
    /**
     * @var string
     */
    private $creditorId;

    /**
     * SepaConfig constructor.
     * @param string $merchantAccountId
     * @param string $secret
     */
    public function __construct($paymentMethodName, $merchantAccountId, $secret)
    {
        parent::__construct($paymentMethodName, $merchantAccountId, $secret);
    }

    /**
     * @param string $creditorId
     */
    public function setCreditorId($creditorId)
    {
        $this->creditorId = $creditorId;
    }

    public function mappedProperties()
    {
        $result = parent::mappedProperties();
        $result['creditor-id'] = $this->creditorId;
        return $result;
    }
}
