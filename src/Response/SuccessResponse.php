<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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
     * @var array
     */
    private $providerTransactionIds;

    /**
     * SuccessResponse constructor.
     * @param \SimpleXMLElement $simpleXml
     * @throws MalformedResponseException
     */
    public function __construct($simpleXml)
    {
        parent::__construct($simpleXml);
        $this->transactionId = $this->findElement('transaction-id');
        $this->providerTransactionIds = $this->findProviderTransactionIds();
        $this->transactionType = $this->findElement('transaction-type');
    }

    /**
     * @return array
     */
    private function findProviderTransactionIds()
    {
        $result = [];
        foreach ($this->simpleXml->{'statuses'}->{'status'} as $status) {
            if (isset($status['provider-transaction-id'])) {
                $result[] = $status['provider-transaction-id'];
            }
        }
        return $result;
    }


    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @deprecated This method is since 3.6.6 deprecated, please use getProviderTransactionIds
     * @return string
     */
    public function getProviderTransactionId()
    {
        return (string) $this->providerTransactionIds[0];
    }

    /**
     * @return array
     * @since 3.6.6
     */
    public function getProviderTransactionIds()
    {
            return $this->providerTransactionIds;
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
