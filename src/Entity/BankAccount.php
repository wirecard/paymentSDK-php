<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

class BankAccount implements MappableEntity
{

    /**
     * @var string
     */
    private $bankName;

    /**
     * @var string
     */
    private $iban;

    /**
     * @var string
     */
    private $bic;
    /**
     * @return array
     */

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $properties = array();

        if (isset($this->bankName)) {
            $properties['bank-name'] = $this->bankName;
        }
        if (isset($this->iban)) {
            $properties['iban'] = $this->iban;
        }
        if (isset($this->bic)) {
            $properties['bic'] = $this->bic;
        }

        return $properties;
    }

    /**
     * @param string
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @param string
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @param string
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }
}
