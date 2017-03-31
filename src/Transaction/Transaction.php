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

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\ItemCollection;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Interface Transaction
 * @package Wirecard\PaymentSdk\Transaction
 */
abstract class Transaction
{
    const PARAM_PAYMENT = 'payment';
    const PARAM_TRANSACTION_TYPE = 'transaction-type';
    const PARAM_PARENT_TRANSACTION_ID = 'parent-transaction-id';
    const ENDPOINT_PAYMENTS = '/engine/rest/payments/';
    const ENDPOINT_PAYMENT_METHODS = '/engine/rest/paymentmethods/';
    const NAME = '';
    const TYPE_AUTHORIZATION = 'authorization';
    const TYPE_AUTHORIZATION_ONLY = 'authorization-only';
    const TYPE_REFERENCED_AUTHORIZATION = 'referenced-authorization';
    const TYPE_CAPTURE_AUTHORIZATION = 'capture-authorization';
    const TYPE_VOID_AUTHORIZATION = 'void-authorization';
    const TYPE_PENDING_CREDIT = 'pending-credit';
    const TYPE_CREDIT = 'credit';
    const TYPE_PENDING_DEBIT = 'pending-debit';
    const TYPE_DEBIT = 'debit';
    const TYPE_REFUND_CAPTURE = 'refund-capture';
    const TYPE_REFUND_DEBIT = 'refund-debit';
    const TYPE_VOID_CAPTURE = 'void-capture';

    /**
     * @var AccountHolder
     */
    protected $accountHolder;

    /**
     * @var Amount
     */
    protected $amount;

    /**
     * @var string
     */
    protected $consumerId;

    /**
     * @var ItemCollection
     */
    protected $itemCollection;

    /**
     * @var string
     */
    protected $notificationUrl;

    /**
     * @var string
     */
    protected $operation;

    /**
     * @var string
     */
    protected $parentTransactionId;

    /**
     * @var string
     */
    protected $parentTransactionType;

    /**
     * @var string
     */
    protected $requestId;

    /**
     * @var Redirect
     */
    protected $redirect;

    /**
     * @param AccountHolder $accountHolder
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @param Amount $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @param ItemCollection $itemCollection
     * @return Transaction
     */
    public function setItemCollection(ItemCollection $itemCollection)
    {
        $this->itemCollection = $itemCollection;
        return $this;
    }

    /**
     * @return string
     */
    public function getParentTransactionId()
    {
        return $this->parentTransactionId;
    }

    /**
     * @param string $parentTransactionId
     */
    public function setParentTransactionId($parentTransactionId)
    {
        $this->parentTransactionId = $parentTransactionId;
    }

    /**
     * @param string $parentTransactionType
     */
    public function setParentTransactionType($parentTransactionType)
    {
        $this->parentTransactionType = $parentTransactionType;
    }

    /**
     * @param mixed $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @param string $notificationUrl
     */
    public function setNotificationUrl($notificationUrl)
    {
        $this->notificationUrl = $notificationUrl;
    }

    /**
     * @param string $consumerId
     */
    public function setConsumerId($consumerId)
    {
        $this->consumerId = $consumerId;
    }

    /**
     * @param string $operation
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
    }

    /**
     * @throws MandatoryFieldMissingException
     * @throws UnsupportedOperationException
     * @return array
     *
     * A template method for the mapping of the transaction properties:
     *  - the common properties are mapped here,
     *  - an abstract operation is defined for the payment type specific properties.
     */
    public function mappedProperties()
    {
        $result = ['payment-methods' => ['payment-method' => [[
            'name' => $this->paymentMethodNameForRequest()
        ]]]];

        if ($this->amount instanceof Amount) {
            $result['requested-amount'] = $this->amount->mappedProperties();
        }

        if ($this->accountHolder instanceof AccountHolder) {
            $result['account-holder'] = $this->accountHolder->mappedProperties();
        }

        if (null !== $this->parentTransactionId) {
            $result[self::PARAM_PARENT_TRANSACTION_ID] = $this->parentTransactionId;
        }

        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $result['ip-address'] = $_SERVER['REMOTE_ADDR'];
        }

        if (null !== $this->notificationUrl) {
            $onlyNotificationUrl = [
                'notification' => [['url' => $this->notificationUrl]]
            ];
            $result['notifications'] = $onlyNotificationUrl;
        }

        if ($this->redirect instanceof Redirect) {
            $result['cancel-redirect-url'] = $this->redirect->getCancelUrl();
            $result['success-redirect-url'] = $this->redirect->getSuccessUrl();
            if ($this->redirect->getFailureUrl()) {
                $result['redirect-url'] = $this->redirect->getFailureUrl();
            }
        }

        if ($this->itemCollection instanceof ItemCollection) {
            $result['order-items'] = $this->itemCollection->mappedProperties();
        }

        if (null !== $this->consumerId) {
            $result['consumer-id'] = $this->consumerId;
        }

        $result[self::PARAM_TRANSACTION_TYPE] = $this->retrieveTransactionType();

        return array_merge($result, $this->mappedSpecificProperties());
    }

    /**
     * @return string
     */
    protected function paymentMethodNameForRequest()
    {
        return $this->getConfigKey();
    }

    /**
     * @param string|null
     * @return string
     */
    public function getConfigKey()
    {
        return $this::NAME;
    }

    /**
     * @throws UnsupportedOperationException|MandatoryFieldMissingException
     * @return string
     */
    protected function retrieveTransactionType()
    {
        switch ($this->operation) {
            case Operation::RESERVE:
                $transactionType = $this->retrieveTransactionTypeForReserve();
                break;
            case Operation::PAY:
                $transactionType = $this->retrieveTransactionTypeForPay();
                break;
            case Operation::CANCEL:
                $transactionType = $this->retrieveTransactionTypeForCancel();
                break;
            case Operation::CREDIT:
                $transactionType = $this->retrieveTransactionTypeForCredit();
                break;
            default:
                throw new UnsupportedOperationException();
        }

        return $transactionType;
    }

    /**
     * @throws UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        throw new UnsupportedOperationException();
    }

    /**
     * @throws UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        throw new UnsupportedOperationException();
    }

    /**
     * @throws UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        throw new UnsupportedOperationException();
    }

    /**
     * @throws UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCredit()
    {
        throw new UnsupportedOperationException();
    }

    /**
     * @return array
     */
    abstract protected function mappedSpecificProperties();

    /**
     * return string
     */
    public function getEndpoint()
    {
        return self::ENDPOINT_PAYMENT_METHODS;
    }

    /**
     * @param Redirect $redirect
     * @return Transaction
     */
    public function setRedirect(Redirect $redirect)
    {
        $this->redirect = $redirect;
        return $this;
    }

    public function getSuccessUrl()
    {
        if (null === $this->redirect) {
            return null;
        }

        return $this->redirect->getSuccessUrl();
    }
}
