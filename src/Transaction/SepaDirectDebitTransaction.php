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

class SepaDirectDebitTransaction extends Transaction implements Reservable
{
    const NAME = 'sepadirectdebit';

    /**
     * Maximum number of characters: 100
     */
    const DESCRIPTOR_LENGTH = 100;

    /**
     * @var bool
     */
    protected $sepaCredit = true;

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
        $this->iban = str_replace(' ', '', $iban);
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
        if ($this->parentTransactionType != self::TYPE_PENDING_DEBIT) {
            throw new UnsupportedOperationException('The transaction cannot be canceled.');
        }
        return 'void-' . $this->parentTransactionType;
    }
}
