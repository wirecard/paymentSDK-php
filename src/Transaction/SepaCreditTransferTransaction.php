<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

class SepaCreditTransferTransaction extends Transaction implements Reservable
{
    const NAME = 'sepacredit';

    /**
     * Maximum number of characters: 100
     */
    const DESCRIPTOR_LENGTH = 100;

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
     * @since 3.0.0
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
     * @since 3.0.0
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = str_replace(' ', '', $iban);
    }

    /**
     * @since 3.0.0
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

        return $result;
    }

    /**
     * @since 3.0.0
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }
        if ($this->parentTransactionType != self::TYPE_PENDING_CREDIT) {
            throw new UnsupportedOperationException('The transaction cannot be canceled.');
        }
        return 'void-' . $this->parentTransactionType;
    }

    /**
     * @since 3.0.0
     * @return string
     */
    protected function retrieveTransactionTypeForCredit()
    {
        return self::TYPE_CREDIT;
    }
}
