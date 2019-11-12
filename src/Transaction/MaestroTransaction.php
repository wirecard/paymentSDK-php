<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class MaestroTransaction
 * @package Wirecard\PaymentSdk\Transaction
 * @since 3.2.0
 */
class MaestroTransaction extends CreditCardTransaction
{
    const NAME = 'maestro';

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     * @since 3.2.0
     */
    protected function mappedSpecificProperties()
    {
        $result = parent::mappedSpecificProperties();
        $result['payment-methods'] = ['payment-method' => [[
            'name' => CreditCardTransaction::NAME
        ]]];

        return $result;
    }

    /**
     * Maestro transactions are 3d
     * @return bool
     * @since 3.2.0
     */
    protected function isThreeD()
    {
        return true;
    }
}
