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
* Class PayolutionInvoiceTransaction
* @package Wirecard\PaymentSdk\Transaction
* @since 3.4.0
*/
class PayolutionInvoiceTransaction extends Transaction implements Reservable
{
    const NAME = 'payolution-inv';

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $result = [
            'order-number' => $this->orderNumber,
        ];

        if (null !== $this->accountHolder) {
            $result['account-holder'] = $this->accountHolder->mappedProperties();
            $result['account-holder']['date-of-birth'] = $this->getAccountHolder()->getDateOfBirth('Y-m-d');
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        return self::TYPE_AUTHORIZATION;
    }

    /**
     * @throws MandatoryFieldMissingException
     * @return mixed
     */
    protected function retrieveTransactionTypeForPay()
    {
        if ($this->parentTransactionId) {
            return self::TYPE_CAPTURE_AUTHORIZATION;
        }

        throw new MandatoryFieldMissingException('Parent transaction id is missing for pay operation.');
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }
        if ($this->parentTransactionType === self::TYPE_AUTHORIZATION) {
            return self::TYPE_VOID_AUTHORIZATION;
        } elseif ($this->parentTransactionType === self::TYPE_CAPTURE_AUTHORIZATION) {
            return self::TYPE_REFUND_CAPTURE;
        }

        throw new UnsupportedOperationException('The transaction can not be canceled.');
    }

    /**
     * return string
     */
    public function getEndpoint()
    {
        if ($this->operation === Operation::RESERVE) {
            return self::ENDPOINT_PAYMENT_METHODS;
        }

        return self::ENDPOINT_PAYMENTS;
    }
}
