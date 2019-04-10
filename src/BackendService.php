<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk;

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\MaestroTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class BackendService
 *
 * This service manages backend operations
 * @package Wirecard\PaymentSdk
 * @extends TransactionService
 */
class BackendService extends TransactionService
{
    const TYPE_AUTHORIZED = 'authorized';
    const TYPE_CANCELLED = 'cancelled';
    const TYPE_PROCESSING = 'processing';
    const TYPE_REFUNDED = 'refunded';
    const TYPE_PENDING = 'pending';

    const CANCEL_BUTTON_TEXT = 'Cancel';
    const REFUND_BUTTON_TEXT = 'Refund';
    const CAPTURE_BUTTON_TEXT = 'Capture';
    const CREDIT_BUTTON_TEXT = 'Credit';

    /**
     * Method returns possible follow up operations for transaction.
     * If limit is set to false (default), all possible operations will be returned.
     *
     * @param Transaction $transaction
     * @param boolean $limit
     * @return array|bool
     */
    public function retrieveBackendOperations($transaction, $limit = false)
    {
        $parentTransaction = $this->getTransactionByTransactionId(
            $transaction->getParentTransactionId(),
            $transaction::NAME != MaestroTransaction::NAME ? $transaction::NAME : CreditCardTransaction::NAME
        );
        if (!is_null($parentTransaction) && (
            !$limit ||
            !$this->isFinal($parentTransaction[Transaction::PARAM_PAYMENT][Transaction::PARAM_TRANSACTION_TYPE])
            )) {
            $transaction->setParentTransactionType(
                $parentTransaction[Transaction::PARAM_PAYMENT][Transaction::PARAM_TRANSACTION_TYPE]
            );
        } else {
            return false;
        }

        $operations = false;
        if ($transaction->getBackendOperationForPay() && (!$limit ||
            $transaction->getParentTransactionType() == Transaction::TYPE_AUTHORIZATION)) {
            $operations[Operation::PAY] = self::CAPTURE_BUTTON_TEXT;
        }
        if ($transaction->getBackendOperationForCancel()) {
            if (stristr($transaction->getBackendOperationForCancel(), 'refund')) {
                $operations[Operation::CANCEL] = self::REFUND_BUTTON_TEXT;
            } else {
                $operations[Operation::CANCEL] = self::CANCEL_BUTTON_TEXT;
            }
        }
        if ($transaction->getBackendOperationForRefund()) {
            $operations[Operation::REFUND] = self::REFUND_BUTTON_TEXT;
        }
        if ($transaction->getBackendOperationForCredit() || $transaction->getSepaCredit()) {
            if ($limit && $transaction->getSepaCredit()) {
                $operations[Operation::CREDIT] = self::REFUND_BUTTON_TEXT;
            } elseif (!$limit) {
                $operations[Operation::CREDIT] = self::CREDIT_BUTTON_TEXT;
            }
        }

        return $operations;
    }

    /**
     * Build in fallback for refund
     *
     * @param Transaction $transaction
     * @param string $operation
     * @return FailureResponse|Response\InteractionResponse|Response\Response|Response\SuccessResponse
     */
    public function process(Transaction $transaction, $operation)
    {
        $response = parent::process($transaction, $operation);

        if ($response instanceof FailureResponse && $operation == Operation::CANCEL
            && $transaction->getBackendOperationForRefund()) {
            return parent::process($transaction, Operation::REFUND);
        } else {
            return $response;
        }
    }

    /**
     * Return order state of the transaction
     *
     * @param $transaction_type
     * @return string
     */
    public function getOrderState($transaction_type)
    {
        switch ($transaction_type) {
            case Transaction::TYPE_PENDING_CREDIT:
            case Transaction::TYPE_PENDING_DEBIT:
                $state = self::TYPE_PENDING;
                break;
            case Transaction::TYPE_CAPTURE_AUTHORIZATION:
            case Transaction::TYPE_DEBIT:
            case Transaction::TYPE_PURCHASE:
            case Transaction::TYPE_DEPOSIT:
                $state = self::TYPE_PROCESSING;
                break;
            case Transaction::TYPE_VOID_AUTHORIZATION:
                $state = self::TYPE_CANCELLED;
                break;
            case Transaction::TYPE_REFUND_CAPTURE:
            case Transaction::TYPE_REFUND_DEBIT:
            case Transaction::TYPE_REFUND_PURCHASE:
            case Transaction::TYPE_CREDIT:
            case Transaction::TYPE_VOID_CAPTURE:
            case Transaction::TYPE_VOID_PURCHASE:
                $state = self::TYPE_REFUNDED;
                break;
            case Transaction::TYPE_AUTHORIZATION:
            default:
                $state = self::TYPE_AUTHORIZED;
                break;
        }

        return $state;
    }

    /**
     * Check if the transaction is final
     *
     * @param $transaction_type
     * @return bool
     */
    public function isFinal($transaction_type)
    {
        if (in_array($transaction_type, [
            Transaction::TYPE_CAPTURE_AUTHORIZATION,
            Transaction::TYPE_DEBIT,
            Transaction::TYPE_PURCHASE,
            Transaction::TYPE_AUTHORIZATION,
            Transaction::TYPE_PENDING_CREDIT,
            Transaction::TYPE_PENDING_DEBIT,
            Transaction::TYPE_AUTHORIZATION_ONLY,
            Transaction::TYPE_CHECK_ENROLLMENT,
            Transaction::TYPE_REFERENCED_AUTHORIZATION
        ])) {
            return false;
        }

        return true;
    }
}
