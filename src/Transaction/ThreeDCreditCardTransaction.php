<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class ThreeDCreditCardTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class ThreeDCreditCardTransaction extends CreditCardTransaction
{
    const TYPE_CHECK_ENROLLMENT = 'check-enrollment';
    /**
     * @var string
     */
    private $termUrl;

    /**
     * @var string
     */
    private $paRes;

    /**
     * @return string
     */
    public function getTermUrl()
    {
        return $this->termUrl;
    }

    /**
     * @param string $termUrl
     * @return $this
     */
    public function setTermUrl($termUrl)
    {
        $this->termUrl = $termUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaRes()
    {
        return $this->paRes;
    }

    /**
     * @param string $paRes
     * @return ThreeDCreditCardTransaction
     */
    public function setPaRes($paRes)
    {
        $this->paRes = $paRes;

        return $this;
    }

    /**
     * @param string|null $operation
     * @param null|string $parentTransactionType
     * @return array
     */
    public function mappedProperties($operation = null, $parentTransactionType = null)
    {
        $result = parent::mappedProperties($operation, $parentTransactionType);

        if (null !== $this->paRes) {
            $result['three-d'] = [
                'pares' => $this->paRes,
            ];
        }

        return $result;
    }

    /**
     * @param string|null $operation
     * @param null|string $parentTransactionType
     * @throws UnsupportedOperationException|MandatoryFieldMissingException
     * @return string
     */
    protected function retrieveTransactionType($operation, $parentTransactionType)
    {
        if (null !== $this->paRes) {
            return $operation;
        }

        switch ($operation) {
            case Operation::RESERVE:
                $transactionType = $this->retrieveTransactionTypeForReserve($parentTransactionType);
                break;
            case Operation::CANCEL:
                $transactionType = $this->retrieveTransactionTypeForCancel($parentTransactionType);
                break;
            case Operation::PAY:
                $transactionType = $this->retrieveTransactionTypeForPay($parentTransactionType);
                break;
            default:
                throw new UnsupportedOperationException();
        }

        return $transactionType;
    }

    /**
     * @param string $parentTransactionType
     * @return string
     */
    protected function retrieveTransactionTypeForReserve($parentTransactionType)
    {
        switch ($parentTransactionType) {
            case $this::TYPE_AUTHORIZATION:
                $transactionType = $this::TYPE_REFERENCED_AUTHORIZATION;
                break;
            case $this::TYPE_CHECK_ENROLLMENT:
                $transactionType = $this::TYPE_AUTHORIZATION;
                break;
            default:
                $transactionType = $this::TYPE_CHECK_ENROLLMENT;
        }

        return $transactionType;
    }

    /**
     * @param string $parentTransactionType
     * @return string
     */
    protected function retrieveTransactionTypeForPay($parentTransactionType)
    {
        switch ($parentTransactionType) {
            case $this::TYPE_AUTHORIZATION:
                $transactionType = $this::TYPE_CAPTURE_AUTHORIZATION;
                break;
            case $this::TYPE_PURCHASE:
                $transactionType = $this::TYPE_REFERENCED_PURCHASE;
                break;
            case $this::TYPE_CHECK_ENROLLMENT:
                $transactionType = $this::TYPE_PURCHASE;
                break;
            default:
                $transactionType = $this::TYPE_CHECK_ENROLLMENT;
        }

        return $transactionType;
    }
}
