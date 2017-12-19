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

use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

class SepaTransaction extends Transaction implements Reservable
{
    const NAME = 'sepa';

    const DIRECT_DEBIT = 'sepadirectdebit';
    const CREDIT_TRANSFER = 'sepacredit';

    /**
     * @var string
     */
    private $iban;

    /**
     * @var string
     */
    private $bic;

    /**
     * @var Mandate
     */
    private $mandate;

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @param Mandate $mandate
     */
    public function setMandate($mandate)
    {
        $this->mandate = $mandate;
    }

    /**
     * @throws UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $result = array();

        if (null !== $this->iban) {
            $result['bank-account'] = ['iban' => $this->iban];
            if (null !== $this->bic) {
                $result['bank-account']['bic'] = $this->bic;
            }
        }

        if (null !== $this->mandate) {
            $result['mandate'] = $this->mandate->mappedProperties();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getConfigKey()
    {
        if (Operation::CREDIT === $this->operation
            || self::TYPE_CREDIT === $this->parentTransactionType
            || self::TYPE_PENDING_CREDIT === $this->parentTransactionType
        ) {
            return self::CREDIT_TRANSFER;
        }

        return self::DIRECT_DEBIT;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        return self::TYPE_AUTHORIZATION;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        return self::TYPE_DEBIT;
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
        if (!in_array($this->parentTransactionType, [self::TYPE_PENDING_DEBIT, self::TYPE_PENDING_CREDIT], false)) {
            throw new UnsupportedOperationException('The transaction can not be canceled.');
        }
        return 'void-' . $this->parentTransactionType;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForCredit()
    {
        return self::TYPE_CREDIT;
    }
}
