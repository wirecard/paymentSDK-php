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
     * @return array
     */
    public function mappedProperties($operation = null)
    {
        if ($this->tokenId === null && $this->parentTransactionId === null) {
            throw new MandatoryFieldMissingException(
                'At least one of these two parameters has to be provided: token ID, parent transaction ID.'
            );
        }

        $result = parent::mappedProperties($operation);

        $result[self::PARAM_TRANSACTION_TYPE] = $this->retrieveTransactionType($operation);

        if (null !== $this->tokenId) {
            $result['card-token'] = [
                'token-id' => $this->tokenId,
            ];
        }

        return $result;
    }

    private function retrieveTransactionType($operation)
    {
        $transactionTypes = [
            Operation::RESERVE => $this->retrieveTransactionTypeForReserve(),
            Operation::CANCEL => 'void-authorization'
        ];

        if (!array_key_exists($operation, $transactionTypes)) {
            throw new UnsupportedOperationException();
        }

        return $transactionTypes[$operation];
    }

    /**
     * @return string
     */
    private function retrieveTransactionTypeForReserve()
    {
        $transactionType = 'authorization';
        if (null !== $this->parentTransactionId) {
            $transactionType = 'referenced-authorization';
        }

        if ($this instanceof ThreeDCreditCardTransaction) {
            return 'check-enrollment';
        }
        return $transactionType;
    }
}
