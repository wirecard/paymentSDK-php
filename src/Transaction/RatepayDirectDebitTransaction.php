<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
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
