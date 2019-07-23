<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\BankAccount;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

/**
 * Class RatepayDirectDebitTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class RatepayDirectDebitTransaction extends RatepayTransaction implements Reservable
{
    const NAME = 'ratepay-elv';

    /**
     * @var string
     */
    private $creditorId;

    /**
     * @var string
     */
    private $mandate;

    /**
     * @var BankAccount
     */
    private $bankAccount;

    /**
     * @param string
     */
    public function setCreditorId($creditorId)
    {
        $this->creditorId = $creditorId;
    }

    /**
     * @param string
     */
    public function setMandate($mandateId)
    {
        $this->mandate = [
            'mandate-id' => $mandateId,
            'signed-date' => date('d-m-Y')
        ];
    }

    /**
     * @param BankAccount
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;
    }

    protected function mappedSpecificProperties()
    {
        $ratepayProperties = parent::mappedSpecificProperties();

        if (null === $this->bankAccount && Operation::RESERVE === $this->operation) {
            throw new MandatoryFieldMissingException('Bank account is a mandatory field.');
        }

        $directDebitProperties = [
            'creditor-id' => $this->creditorId,
            'mandate' => $this->mandate
        ];

        if (null !== $this->bankAccount) {
            $directDebitProperties['bank-account'] = $this->bankAccount->mappedProperties();
        }

        return array_merge($ratepayProperties, $directDebitProperties);
    }
}
