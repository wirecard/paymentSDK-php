<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\CompanyInfo;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
* Class PayolutionBtwobTransaction
* @package Wirecard\PaymentSdk\Transaction
*/
class PayolutionBtwobTransaction extends CustomFieldTransaction implements Reservable
{
    const NAME = 'payolution-b2b';

    /** @var CompanyInfo */
    protected $companyInfo;

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $result = [
            'order-number'  => $this->orderNumber
        ];

        if ($this->companyInfo instanceof CompanyInfo) {
            $map = [
                'company-name' => $this->companyInfo->getCompanyName(),
                'company-uid' => $this->companyInfo->getCompanyUid(),
                'company-trade-register-number' => $this->companyInfo->getCompanyTradeRegisterNumber(),
                'company-register-key' => $this->companyInfo->getCompanyRegisterKey(),
            ];
            foreach ($map as $fieldName => $fieldValue) {
                $this->setRawCustomField($fieldName, $fieldValue);
            }
            if (null !== $this->customFields) {
                $result['custom-fields'] = $this->customFields->mappedProperties();
            }
        };

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
     * @return string
     */
    public function getEndpoint()
    {
        if ($this->operation === Operation::RESERVE) {
            return self::ENDPOINT_PAYMENT_METHODS;
        }

        return self::ENDPOINT_PAYMENTS;
    }

    /**
     * Update all customfields for company information at once
     *
     * @param CompanyInfo $companyInfo
     */
    public function setCompanyInfo($companyInfo)
    {
        $this->companyInfo = $companyInfo;
    }
}
