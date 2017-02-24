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

use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Entity\PaymentMethod\CreditCard;
use Wirecard\PaymentSdk\Entity\PaymentMethod\ThreeDCreditCard;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

/**
 * Class ReserveTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class ReserveTransaction implements Transaction
{
    const CCARD_AUTHORIZATION = 'authorization';
    const PARAM_PARENT_TRANSACTION_ID = 'parent-transaction-id';

    /**
     * @var Money
     */
    private $amount;

    /**
     * @var string
     */
    private $parentTransactionId;

    /**
     * @var CreditCard
     */
    private $paymentTypeSpecificData;

    /**
     * @return Money
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Money $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     * @return CreditCard
     */
    public function getPaymentTypeSpecificData()
    {
        return $this->paymentTypeSpecificData;
    }

    /**
     * @param CreditCard $paymentTypeSpecificData
     */
    public function setPaymentTypeSpecificData($paymentTypeSpecificData)
    {
        $this->paymentTypeSpecificData = $paymentTypeSpecificData;
    }

    /**
     * @return array
     * @throws \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function mappedProperties()
    {
        $tokenId = null !== $this->paymentTypeSpecificData ?
            $this->paymentTypeSpecificData->getTokenId()
            : null;

        if ($tokenId === null && $this->parentTransactionId === null) {
            throw new MandatoryFieldMissingException(
                'At least one of these two parameters has to be provided: token ID, parent transaction ID.'
            );
        }

        $specificProperties = [
            'requested-amount' => $this->amount->mappedProperties(),
            self::PARAM_TRANSACTION_TYPE => self::CCARD_AUTHORIZATION
        ];

        if (null !== $this->parentTransactionId) {
            $specificProperties[self::PARAM_TRANSACTION_TYPE] = 'referenced-authorization';
            $specificProperties[self::PARAM_PARENT_TRANSACTION_ID] = $this->parentTransactionId;
        }

        if (null !== $tokenId) {
            $specificProperties['card-token'] = [
                'token-id' => $tokenId,
            ];
        }

        $specificProperties['ip-address'] = $_SERVER['REMOTE_ADDR'];

        if ($this->paymentTypeSpecificData instanceof ThreeDCreditCard) {
            $specificProperties[self::PARAM_TRANSACTION_TYPE] = 'check-enrollment';
        }

        return $specificProperties;
    }
}
