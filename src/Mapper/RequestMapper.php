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

namespace Wirecard\PaymentSdk\Mapper;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\Periodic;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class RequestMapper
 * @package Wirecard\PaymentSdk\Mapper
 */
class RequestMapper
{
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
     * @throws UnconfiguredPaymentMethodException
     * @throws MandatoryFieldMissingException
     * @return string The transaction in JSON format.
     */
    public function map(Transaction $transaction)
    {
        $requestId = call_user_func($this->requestIdGenerator);
        $commonProperties = [
            'request-id' => $requestId
        ];
        $transaction->setRequestId($requestId);

        $configKey = $transaction->getConfigKey();
        $paymentMethodConfig = $this->config->get($configKey);
        $paymentMethodConfigProperties = $paymentMethodConfig->mappedProperties();

        $allProperties = array_merge(
            $commonProperties,
            $paymentMethodConfigProperties,
            $transaction->mappedProperties()
        );

        $result = [Transaction::PARAM_PAYMENT => $allProperties];

        return json_encode($result);
    }

    /**
     * @param Transaction $transaction
     * @param array $requestData
     * @return array
     */
    public function mapSeamlessRequest(Transaction $transaction, $requestData)
    {
        $accountHolder = $transaction->getAccountHolder();
        $shipping = $transaction->getShipping();
        $basket = $transaction->getBasket();
        $device = $transaction->getDevice();
        $customFields = $transaction->getCustomFields();
        $periodic = $transaction->getPeriodic();

        if ($accountHolder instanceof AccountHolder) {
            $accountHolder = $accountHolder->mappedSeamlessProperties();
            $requestData = array_merge($requestData, $accountHolder);
        }

        if ($shipping instanceof AccountHolder) {
            $shipping = $shipping->mappedSeamlessProperties(AccountHolder::SHIPPING);
            $requestData = array_merge($requestData, $shipping);
        }

        if ($basket instanceof Basket) {
            $basket = $basket->mappedSeamlessProperties();
            $requestData = array_merge($requestData, $basket);
        }

        if ($customFields instanceof CustomFieldCollection) {
            $requestData = array_merge($requestData, $customFields->mappedSeamlessProperties());
        }

        if (strlen($transaction->getNotificationUrl())) {
            $requestData['notification_transaction_url'] = $transaction->getNotificationUrl();
            $requestData['notifications_format'] = 'application/xml';
        }

        if (null !== $transaction->getDescriptor()) {
            $requestData['descriptor'] = $transaction->getDescriptor();
        }

        if (null !== $transaction->getOrderNumber()) {
            $requestData['order_number'] = $transaction->getOrderNumber();
        }

        if (null !== $transaction->getIpAddress()) {
            $requestData['ip_address'] = $transaction->getIpAddress();
        }

        if (null !== $transaction->getConsumerId()) {
            $requestData['consumer_id'] = $transaction->getConsumerId();
        }

        if ($device instanceof Device) {
            $requestData['device_fingerprint'] = $device->getFingerprint();
        }

        if ($periodic instanceof Periodic) {
            $requestData = array_merge($requestData, $periodic->mappedSeamlessProperties());
        }

        return $requestData;
    }
}
