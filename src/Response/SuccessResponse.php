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

namespace Wirecard\PaymentSdk\Response;

use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class SuccessResponse
 * @package Wirecard\PaymentSdk\Response
 */
class SuccessResponse extends Response
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $providerTransactionId;

    /**
     * SuccessResponse constructor.
     * @param \SimpleXMLElement $simpleXml
     * @throws MalformedResponseException
     */
    public function __construct($simpleXml)
    {
        parent::__construct($simpleXml);
        $this->transactionId = $this->findElement('transaction-id');
        $this->providerTransactionId = $this->findProviderTransactionId();
        $this->transactionType = $this->findElement('transaction-type');
    }

    /**
     * @return string
     * @throws MalformedResponseException
     */
    private function findProviderTransactionId()
    {
        $result = null;
        foreach ($this->simpleXml->{'statuses'}->{'status'} as $status) {
            if ($result === null) {
                $result = $status['provider-transaction-id'];
            }

            if (isset($status['provider-transaction-id']) &&
                strcmp($result, $status['provider-transaction-id']) !== 0) {
                throw new MalformedResponseException('More different provider transaction ID-s in response.');
            }
        }

        return (string)$result;
    }


    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getProviderTransactionId()
    {
        return $this->providerTransactionId;
    }

    /**
     * @return null|string
     */
    public function getPaymentMethod()
    {
        if (isset($this->simpleXml->{'payment-methods'})
            && count($this->simpleXml->{'payment-methods'}->{'payment-method'}) > 0
        ) {
            $paymentMethodXml = $this->simpleXml->{'payment-methods'}->{'payment-method'}[0];
            /** @var \SimpleXMLElement $paymentMethodXml */
            $attributes = $paymentMethodXml->attributes();
            if (isset($attributes['name'])) {
                return (string)$attributes['name'];
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getParentTransactionId()
    {
        if (isset($this->simpleXml->{'parent-transaction-id'})) {
            return (string)$this->simpleXml->{'parent-transaction-id'};
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMaskedAccountNumber()
    {
        if (isset($this->simpleXml->{'card-token'}->{'masked-account-number'})) {
            return (string)$this->simpleXml->{'card-token'}->{'masked-account-number'};
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getCardholderAuthenticationStatus()
    {
        if (isset($this->simpleXml->{'three-d'}->{'cardholder-authentication-status'})) {
            return (string)$this->simpleXml->{'three-d'}->{'cardholder-authentication-status'};
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getProviderTransactionReference()
    {
        if (isset($this->simpleXml->{'provider-transaction-reference-id'})) {
            return (string)$this->simpleXml->{'provider-transaction-reference-id'};
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getCardTokenId()
    {
        if (isset($this->simpleXml->{'card-token'}->{'token-id'})) {
            return (string)$this->simpleXml->{'card-token'}->{'token-id'};
        }
        return null;
    }
}
