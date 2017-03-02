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

use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

/**
 * Class CreditCardTransaction
 * @package Wirecard\PaymentSdk\Transaction
 *
 * Use it for SSL payments.
 * For the 3D payments use the specific subclass.
 */
class CreditCardTransaction extends Transaction
{
    const ENDPOINT = '/engine/rest/payments/';
    const NAME = 'creditcard';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_REFERENCED_PURCHASE = 'referenced-purchase';

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
     * @param string|null $operation
     * @param null|string $parentTransactionType
     * @return array
     */
    public function mappedProperties($operation = null, $parentTransactionType = null)
    {
        if ($this->tokenId === null && ($this->parentTransactionId === null && get_class($this) === self::class)) {
            throw new MandatoryFieldMissingException(
                'At least one of these two parameters has to be provided: token ID, parent transaction ID.'
            );
        }

        $result = parent::mappedProperties($operation, $parentTransactionType);
        $result[self::PARAM_TRANSACTION_TYPE] = $this->retrieveTransactionType($operation, $parentTransactionType);

        if (null !== $this->tokenId) {
            $result['card-token'] = [
                'token-id' => $this->tokenId,
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
            case Operation::CREDIT:
                $transactionType = $this::TYPE_CREDIT;
                break;
            default:
                throw new UnsupportedOperationException();
        }

        return $transactionType;
    }

    /**
     * @param string $parentTransactionType
     * @throws MandatoryFieldMissingException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel($parentTransactionType)
    {
        switch ($parentTransactionType) {
            case $this::TYPE_AUTHORIZATION:
            case $this::TYPE_REFERENCED_AUTHORIZATION:
                $transactionType = $this::TYPE_VOID_AUTHORIZATION;
                break;
            case 'refund-capture':
            case 'refund-purchase':
            case 'credit':
                $transactionType = 'void-' . $parentTransactionType;
                break;
            case $this::TYPE_PURCHASE:
            case $this::TYPE_REFERENCED_PURCHASE:
                $transactionType = 'void-purchase';
                break;
            case $this::TYPE_CAPTURE_AUTHORIZATION:
                $transactionType = 'void-capture';
                break;
            default:
                throw new MandatoryFieldMissingException(
                    'Parent transaction type is missing for cancel operation'
                );
        }

        return $transactionType;
    }

    /**
     * @param string $parentTransactionType
     * @return string
     */
    protected function retrieveTransactionTypeForReserve($parentTransactionType)
    {
        $transactionType = $this::TYPE_AUTHORIZATION;

        if (null !== $this->parentTransactionId) {
            $transactionType = $this::TYPE_REFERENCED_AUTHORIZATION;
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
            default:
                $transactionType = $this::TYPE_PURCHASE;
        }

        return $transactionType;
    }
}
