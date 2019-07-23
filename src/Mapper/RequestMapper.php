<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Mapper;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\CardHolderAccount;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\MerchantRiskIndicator;
use Wirecard\PaymentSdk\Entity\Periodic;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\ThreeDSRequestor;
use Wirecard\PaymentSdk\Entity\Browser;
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
        $redirects = $transaction->getRedirect();
        $threeDSRequestor = $transaction->getThreeDSRequestor();
        $cardHolderAccount = $transaction->getCardHolderAccount();
        $merchantRiskIndicator = $transaction->getMerchantRiskIndicator();
        $browser = $transaction->getBrowser();

        if ($accountHolder instanceof AccountHolder) {
            $requestData = array_merge(
                $requestData,
                $accountHolder->mappedSeamlessProperties()
            );
        }

        if ($shipping instanceof AccountHolder) {
            $requestData = array_merge(
                $requestData,
                $shipping->mappedSeamlessProperties(AccountHolder::SHIPPING)
            );
        }

        if ($basket instanceof Basket) {
            $basket = $basket->mappedSeamlessProperties();
            $requestData = array_merge($requestData, $basket);
        }

        if ($customFields instanceof CustomFieldCollection) {
            $requestData = array_merge($requestData, $customFields->mappedSeamlessProperties());
        }

        if ($periodic instanceof Periodic) {
            $requestData = array_merge($requestData, $periodic->mappedSeamlessProperties());
        }

        if ($redirects instanceof Redirect) {
            $requestData = array_merge($requestData, $redirects->mappedSeamlessProperties());
        }

        if ($threeDSRequestor instanceof ThreeDSRequestor) {
            $requestData = array_merge($requestData, $threeDSRequestor->mappedSeamlessProperties());
        }

        if ($cardHolderAccount instanceof CardHolderAccount) {
            $requestData = array_merge($requestData, $cardHolderAccount->mappedSeamlessProperties());
        }

        if ($merchantRiskIndicator instanceof MerchantRiskIndicator) {
            $requestData = array_merge($requestData, $merchantRiskIndicator->mappedSeamlessProperties());
        }

        if ($browser instanceof Browser) {
            $requestData = array_merge($requestData, $browser->mappedSeamlessProperties());
        }

        if ($device instanceof Device) {
            $requestData['device_fingerprint'] = $device->getFingerprint();
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

        return $requestData;
    }
}
