<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Config;

use Wirecard\PaymentSdk\Entity\MappableEntity;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\MaestroTransaction;

class PaymentMethodConfig implements MappableEntity
{
    /**
     * @var string
     */
    protected $paymentMethodName;

    /**
     * @var string
     */
    protected $merchantAccountId;

    /**
     * @var string
     */
    protected $secret;

    /**
     * PaymentMethodConfig constructor.
     * @param string $paymentMethodName
     * @param string|null $merchantAccountId
     * @param string|null $secret
     */
    public function __construct($paymentMethodName, $merchantAccountId = null, $secret = null)
    {
        if (!in_array($paymentMethodName, [CreditCardTransaction::NAME, MaestroTransaction::NAME])
            && (is_null($merchantAccountId) || is_null($secret))) {
            throw new MandatoryFieldMissingException('MAID and secret are mandatory!');
        }
        $this->paymentMethodName = $paymentMethodName;
        $this->merchantAccountId = $merchantAccountId;
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getPaymentMethodName()
    {
        return $this->paymentMethodName;
    }

    /**
     * @return string
     */
    public function getMerchantAccountId()
    {
        return $this->merchantAccountId;
    }

    /**
     * @return string
     */
    public function getThreeDMerchantAccountId()
    {
        return $this->merchantAccountId;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        return [
            'merchant-account-id' => [
                'value' => $this->merchantAccountId
            ]
        ];
    }
}
