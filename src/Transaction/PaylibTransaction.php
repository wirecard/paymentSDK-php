<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

/**
 * Class PaylibTransaction
 *
 * WARNING: This payment method is still in development, please do not use it in its current state
 *
 * @package Wirecard\PaymentSdk\Transaction
 */
class PaylibTransaction extends Transaction
{
    const NAME = 'paylib';

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        if (!$this->accountHolder instanceof AccountHolder && $this->operation === Operation::PAY) {
            throw new MandatoryFieldMissingException('No account holder set.');
        }
        return [];
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        return self::TYPE_DEBIT;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return self::ENDPOINT_PAYMENT_METHODS;
    }
}
