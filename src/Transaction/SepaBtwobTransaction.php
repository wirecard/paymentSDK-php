<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

class SepaBtwobTransaction extends SepaDirectDebitTransaction implements Reservable
{
    /** @var  string $companyName */
    private $companyName;

    /**
     * Set company name
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }


    /**
     * @throws UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $result = parent::mappedSpecificProperties();

        /**
         * The only operation allowed for b2b is PAY,
         * other operations have to be executed without the b2b parameter.o
         */
        if ($this->operation == Operation::PAY) {
            $result['b2b'] = 'true';
        }

        if (null !== $this->companyName) {
            $result['account-holder']['last-name'] = $this->companyName;
        }

        return $result;
    }
}
