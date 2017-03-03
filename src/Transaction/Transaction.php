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
use Wirecard\PaymentSdk\Entity\Money;

/**
 * Interface Transaction
 * @package Wirecard\PaymentSdk\Transaction
 */
abstract class Transaction
{
    const PARAM_PAYMENT = 'payment';
    const PARAM_TRANSACTION_TYPE = 'transaction-type';
    const PARAM_PARENT_TRANSACTION_ID = 'parent-transaction-id';
    const ENDPOINT = '/engine/rest/paymentmethods/';
    const NAME = '';
    const TYPE_AUTHORIZATION = 'authorization';
    const TYPE_REFERENCED_AUTHORIZATION = 'referenced-authorization';
    const TYPE_CAPTURE_AUTHORIZATION = 'capture-authorization';
    const TYPE_VOID_AUTHORIZATION = 'void-authorization';
    const TYPE_CREDIT = 'credit';

    /**
     * @var AccountHolder
     */
    protected $accountHolder;

    /**
     * @var Money
     */
    protected $amount;

    /**
     * @var string
     */
    protected $parentTransactionId;

    /**
     * @var string
     */
    protected $notificationUrl;

    /**
     * @var string
     */
    protected $consumerId;

    /**
     * @param AccountHolder $accountHolder
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @param Money $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @param string $parentTransactionId
     */
    public function setParentTransactionId($parentTransactionId)
    {
        $this->parentTransactionId = $parentTransactionId;
    }

    /**
     * @return string
     */
    public function getParentTransactionId()
    {
        return $this->parentTransactionId;
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
     * @param string|null $operation
     * @param string|null $parentTransactionType
     * @return array
     *
     * A template method for the mapping of the transaction properties:
     *  - the common properties are mapped here,
     *  - an abstract operation is defined for the payment type specific properties.
     */
    public function mappedProperties($operation = null, $parentTransactionType = null)
    {
        $result = ['payment-methods' => ['payment-method' => [['name' => $this->retrievePaymentMethodName()]]]];

        if ($this->amount) {
            $result['requested-amount'] = $this->amount->mappedProperties();
        }

        if ($this->accountHolder) {
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

        if (null !== $this->consumerId) {
            $result['consumer-id'] = $this->consumerId;
        }

        return array_merge($result, $this->mappedSpecificProperties($operation, $parentTransactionType));
    }

    /**
     * @param string $operation
     * @param string $parentTransactionType
     * @return array
     */
    abstract protected function mappedSpecificProperties($operation, $parentTransactionType);

    /**
     * @param string|null
     * @return string
     */
    public function getConfigKey($operation = null, $parentTransactionType = null)
    {
        return get_class($this);
    }

    /**
     * @return string
     */
    public function retrievePaymentMethodName($operation = null, $parentTransactionType = null)
    {
        return $this::NAME;
    }
}
