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
 * Class CreditCardTransaction
 * @package Wirecard\PaymentSdk\Transaction
 *
 * Use it for SSL payments.
 * For the 3D payments use the specific subclass.
 */
class CreditCardTransaction extends Transaction implements Reservable
{
    const NAME = 'creditcard';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_REFERENCED_PURCHASE = 'referenced-purchase';
    const TYPE_REFUND_PURCHASE = 'refund-purchase';
    const TYPE_VOID_PURCHASE = 'void-purchase';

    /**
     * @var string
     */
    private $tokenId;

    /**
     * @param string $tokenId
     */
    public function setTokenId($tokenId)
    {
        $this->tokenId = $tokenId;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return self::ENDPOINT_PAYMENTS;
    }


    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $this->validate();
        $result = array();

        if (null !== $this->tokenId) {
            $result['card-token'] = [
                'token-id' => $this->tokenId,
            ];
        }

        return $result;
    }

    /**
     *
     * @throws \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    protected function validate()
    {
        if ($this->tokenId === null && $this->parentTransactionId === null) {
            throw new MandatoryFieldMissingException(
                'At least one of these two parameters has to be provided: token ID, parent transaction ID.'
            );
        }
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        return (null !== $this->parentTransactionId) ? self::TYPE_REFERENCED_AUTHORIZATION : self::TYPE_AUTHORIZATION;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
                $transactionType = self::TYPE_CAPTURE_AUTHORIZATION;
                break;
            case self::TYPE_PURCHASE:
                $transactionType = self::TYPE_REFERENCED_PURCHASE;
                break;
            default:
                $transactionType = self::TYPE_PURCHASE;
        }

        return $transactionType;
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
        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
            case self::TYPE_REFERENCED_AUTHORIZATION:
                $transactionType = self::TYPE_VOID_AUTHORIZATION;
                break;
            case self::TYPE_REFUND_CAPTURE:
            case self::TYPE_REFUND_PURCHASE:
            case self::TYPE_CREDIT:
                $transactionType = 'void-' . $this->parentTransactionType;
                break;
            case self::TYPE_PURCHASE:
            case self::TYPE_REFERENCED_PURCHASE:
                $transactionType = self::TYPE_VOID_PURCHASE;
                break;
            case self::TYPE_CAPTURE_AUTHORIZATION:
                $transactionType = self::TYPE_VOID_CAPTURE;
                break;
            default:
                throw new UnsupportedOperationException('The transaction can not be canceled.');
        }

        return $transactionType;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForCredit()
    {
        return self::TYPE_CREDIT;
    }

    public function retrieveOperationType()
    {
        return ($this->operation === Operation::RESERVE) ? self::TYPE_AUTHORIZATION : self::TYPE_PURCHASE;
    }
}
