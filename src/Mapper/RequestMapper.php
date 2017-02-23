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

namespace Wirecard\PaymentSdk\Mapper;

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Transaction\CancelTransaction;
use Wirecard\PaymentSdk\Transaction\PayTransaction;
use Wirecard\PaymentSdk\Transaction\ReserveTransaction;
use Wirecard\PaymentSdk\Transaction\ThreeDAuthorizationTransaction;
use Wirecard\PaymentSdk\Entity\PaymentMethod\ThreeDCreditCard;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class RequestMapper
 * @package Wirecard\PaymentSdk\Mapper
 */
class RequestMapper
{
    const PARAM_TRANSACTION_TYPE = 'transaction-type';
    const PARAM_PARENT_TRANSACTION_ID = 'parent-transaction-id';
    const CCARD_AUTHORIZATION = 'authorization';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Closure
     */
    private $requestIdGenerator;

    /**
     * RequestMapper constructor.
     * @param Config $config
     * @param \Closure $requestIdGenerator
     */
    public function __construct(Config $config, \Closure $requestIdGenerator)
    {
        $this->config = $config;
        $this->requestIdGenerator = $requestIdGenerator;
    }

    /**
     * @param Transaction $transaction
     * @return string The transaction in JSON format.
     * @throws \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function map(Transaction $transaction)
    {
        $requestId = call_user_func($this->requestIdGenerator);
        $commonProperties = [
            'merchant-account-id' => ['value' => $this->config->getMerchantAccountId()],
            'request-id' => $requestId
        ];

        $specificProperties = [];

        if ($transaction instanceof PayTransaction) {
            $specificProperties = $this->getSpecificPropertiesForPayPal($transaction);
        }

        if ($transaction instanceof ReserveTransaction) {
            $specificProperties = $this->getSpecificPropertiesForCreditCard($transaction);
        }

        if ($transaction instanceof ThreeDAuthorizationTransaction) {
            $specificProperties = $this->getSpecificPropertiesForReference($transaction);
        }

        if ($transaction instanceof CancelTransaction) {
            $specificProperties = $this->getSpecificPropertiesForFollowup($transaction);
        }

        $allProperties = array_merge($commonProperties, $specificProperties);
        $result = ['payment' => $allProperties];

        return json_encode($result);
    }

    /**
     * @param $transaction
     * @return array
     */
    private function getAmountOfTransaction($transaction)
    {
        return [
            'currency' => $transaction->getAmount()->getCurrency(),
            'value' => $transaction->getAmount()->getAmount()
        ];
    }

    /**
     * @param PayTransaction $transaction
     * @return array
     */
    private function getSpecificPropertiesForPayPal(PayTransaction $transaction)
    {
        $onlyPaymentMethod = ['payment-method' => [['name' => 'paypal']]];
        $onlyNotificationUrl = [
            'notification' => [['url' => $transaction->getPaymentTypeSpecificData()->getNotificationUrl()]]
        ];

        return [
            'requested-amount' => $this->getAmountOfTransaction($transaction),
            self::PARAM_TRANSACTION_TYPE => 'debit',
            'payment-methods' => $onlyPaymentMethod,
            'cancel-redirect-url' => $transaction->getPaymentTypeSpecificData()->getRedirect()->getCancelUrl(),
            'success-redirect-url' => $transaction->getPaymentTypeSpecificData()->getRedirect()->getSuccessUrl(),
            'notifications' => $onlyNotificationUrl
        ];
    }

    /**
     * @param ReserveTransaction $transaction
     * @return array
     * @throws \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    private function getSpecificPropertiesForCreditCard(ReserveTransaction $transaction)
    {
        $tokenId = null !== $transaction->getPaymentTypeSpecificData() ?
            $transaction->getPaymentTypeSpecificData()->getTokenId()
            : null;
        $parentTransactionId = $transaction->getParentTransactionId();

        if ($tokenId === null && $parentTransactionId === null) {
            throw new MandatoryFieldMissingException(
                'At least one of these two parameters has to be provided: token ID, parent transaction ID.'
            );
        }

        $specificProperties = [
            'requested-amount' => $this->getAmountOfTransaction($transaction),
            self::PARAM_TRANSACTION_TYPE => self::CCARD_AUTHORIZATION
        ];

        if (null !== $parentTransactionId) {
            $specificProperties[self::PARAM_TRANSACTION_TYPE] = 'referenced-authorization';
            $specificProperties[self::PARAM_PARENT_TRANSACTION_ID] = $transaction->getParentTransactionId();
        }

        if (null !== $tokenId) {
            $specificProperties['card-token'] = [
                'token-id' => $tokenId,
            ];
        }

        $specificProperties['ip-address'] = $_SERVER['REMOTE_ADDR'];

        if ($transaction->getPaymentTypeSpecificData() instanceof ThreeDCreditCard) {
            $threeDProperties = [
                self::PARAM_TRANSACTION_TYPE => 'check-enrollment',
            ];
            $specificProperties = array_merge($specificProperties, $threeDProperties);
        }

        return $specificProperties;
    }

    /**
     * @param ThreeDAuthorizationTransaction $transaction
     * @return array
     */
    private function getSpecificPropertiesForReference($transaction)
    {
        $payload = $transaction->getPayload();
        $md = json_decode(base64_decode($payload['MD']), true);
        $parentTransactionId = $md['enrollment-check-transaction-id'];
        $paRes = $payload['PaRes'];

        return [
            self::PARAM_TRANSACTION_TYPE => self::CCARD_AUTHORIZATION,
            self::PARAM_PARENT_TRANSACTION_ID => $parentTransactionId,
            'three-d' => [
                'pares' => $paRes
            ],
        ];
    }

    /**
     * @param CancelTransaction $transaction
     * @return array
     */
    private function getSpecificPropertiesForFollowup($transaction)
    {
        return [
            self::PARAM_TRANSACTION_TYPE => 'void-authorization',
            self::PARAM_PARENT_TRANSACTION_ID => $transaction->getParentTransactionId()
        ];
    }
}
