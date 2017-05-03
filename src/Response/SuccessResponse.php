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

            if (strcmp($result, $status['provider-transaction-id']) !== 0) {
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
}
