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
        return $this::ENDPOINT_PAYMENTS;
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
        return (null !== $this->parentTransactionId) ? $this::TYPE_REFERENCED_AUTHORIZATION : $this::TYPE_AUTHORIZATION;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        switch ($this->parentTransactionType) {
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

    /**
     * @throws MandatoryFieldMissingException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        switch ($this->parentTransactionType) {
            case $this::TYPE_AUTHORIZATION:
            case $this::TYPE_REFERENCED_AUTHORIZATION:
                $transactionType = $this::TYPE_VOID_AUTHORIZATION;
                break;
            case 'refund-capture':
            case 'refund-purchase':
            case 'credit':
                $transactionType = 'void-' . $this->parentTransactionType;
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
     * @return string
     */
    protected function retrieveTransactionTypeForCredit()
    {
        return $this::TYPE_CREDIT;
    }
}
