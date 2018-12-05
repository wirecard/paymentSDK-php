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

namespace Wirecard\PaymentSdk\Transaction;

class PayByBankAppTransaction extends Transaction
{
    const NAME = 'zapp';
    
    
    /**
     * @var string
     */
    private $deviceType;

    /**
     * @var string
     */
    private $deviceOperatingSystem;

    /**
     * @var string
     */
    private $merchantReturnString;

    /**
     * @var string
     */
    private $transactionType;

    /**
     * @var string
     */
    private $deliveryType;

    /**
     * @var string
     */
    private $refundReasonType;

    /**
     * @var string
     */
    private $refundMethod;

    /**
     * @var string
     */
    private $merchantRefundRef;

    /**
     * @var string
     */
    private $caseRefId;
    
    /**
     * @var string
     */
    private $browserUserAgent;
    
    /**
     * @var string
     */
    private $browserTimezone;
    
    /**
     * @var string
     */
    private $browserScreenResolution;
    
    /**
     * @var string
     */
    private $pcid;

    /**
     * @param string $deviceType
     * @return PayByBankAppTransaction
     */
    public function setDeviceType($deviceType)
    {
        $this->deviceType = $deviceType;
        return $this;
    }

    /**
     * @param string $deviceOperatingSystem
     * @return PayByBankAppTransaction
     */
    public function setDeviceOperatingSystem($deviceOperatingSystem)
    {
        $this->deviceOperatingSystem = $deviceOperatingSystem;
        return $this;
    }

    /**
     * @param string $merchantReturnString
     * @return PayByBankAppTransaction
     */
    public function setMerchantReturnString($merchantReturnString)
    {
        $this->merchantReturnString = $merchantReturnString;
        return $this;
    }

    /**
     * @param string $transactionType
     * @return PayByBankAppTransaction
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    /**
     * @param string $deliveryType
     * @return PayByBankAppTransaction
     */
    public function setDeliveryType($deliveryType)
    {
        $this->deliveryType = $deliveryType;
        return $this;
    }

    /**
     * @param string $refundReasonType
     * @return PayByBankAppTransaction
     */
    public function setRefundReasonType($refundReasonType)
    {
        $this->refundReasonType = $refundReasonType;
        return $this;
    }
     /**
     * @param string $refundMethod
     * @return PayByBankAppTransaction
     */
    public function setRefundMethod($refundMethod)
    {
        $this->refundMethod = $refundMethod;
        return $this;
    }
     /**
     * @param string $merchantRefundRef
     * @return PayByBankAppTransaction
     */
    public function setMerchantRefundRef($merchantRefundRef)
    {
        $this->merchantRefundRef = $merchantRefundRef;
        return $this;
    }
     /**
     * @param string $caseRefId
     * @return PayByBankAppTransaction
     */
    public function setCaseRefId($caseRefId)
    {
        $this->caseRefId = $caseRefId;
        return $this;
    }

    /**
     * @param string $pcid
     * @return PayByBankAppTransaction
     */
    public function setPcid($pcid)
    {
        $this->pcid = $pcid;
        return $this;
    }
    /**
     * @param string $browserUserAgent
     * @return PayByBankAppTransaction
     */
    public function setBrowserUserAgent($browserUserAgent)
    {
        $this->browserUserAgent = $browserUserAgent;
        return $this;
    }
    /**
     * @param string $browserTimezone
     * @return PayByBankAppTransaction
     */
    public function setBrowserTimezone($browserTimezone)
    {
        $this->browserTimezone = $browserTimezone;
        return $this;
    }
    /**
     * @param string $caseRefId
     * @return PayByBankAppTransaction
     */
    public function setBrowserScreenResolution($browserScreenResolution)
    {
        $this->browserScreenResolution = $browserScreenResolution;
        return $this;
    }

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $result = ['custom-fields' => ['custom-field' => []]];

        $device = array();
        if (null !== $this->deviceType) {
            $device['type'] = $this->deviceType;
        }
        if (null !== $this->deviceOperatingSystem) {
            $device['operating-system'] = $this->deviceOperatingSystem;
        }
        if (count($device) > 0) {
            $result['device'] = $device;
        }

        if (null !== $this->merchantReturnString) {
            $result['custom-fields']['custom-field'][] = $this->mapField('MerchantRtnStrng', $this->merchantReturnString);
        }
        if (null !== $this->transactionType) {
            $result['custom-fields']['custom-field'][] = $this->mapField('TxType', $this->transactionType);
        }
        if (null !== $this->deliveryType) {
            $result['custom-fields']['custom-field'][] = $this->mapField('DeliveryType', $this->deliveryType);
        }

        if (null !== $this->refundReasonType) {
            $result['custom-fields']['custom-field'][] = $this->mapField('RefundReasonType', $this->refundReasonType);
        }
        if (null !== $this->refundMethod) {
            $result['custom-fields']['custom-field'][] = $this->mapField('RefundMethod', $this->refundMethod);
        }
        if (null !== $this->merchantRefundRef) {
            $result['custom-fields']['custom-field'][] = $this->mapField('MerchantRefundRef', $this->merchantRefundRef);
        }
        if (null !== $this->caseRefId) {
            $result['custom-fields']['custom-field'][] = $this->mapField('CaseRefId', $this->caseRefId);
        }

        if (null !== $this->browserUserAgent) {
            $result['browser'] = [
                'user-agent' => $this->browserUserAgent,
                'time-zone' => $this->browserTimezone,
                'screen-resolution' => $this->browserScreenResolution,
                'cookies' => []
            ];
            if (null !== $this->pcid) {
                $result['browser']['cookies'][] = ['cookie' => ['name' => 'pcid', 'value' => $this->pcid] ];
            }
        }

        return $result;
    }

    private function mapField($name, $value)
    {
        return  [
                'field-name' => 'zapp.in.' . $name,
                'field-value' => $value
            ];
    }

    protected function retrieveTransactionTypeForPay()
    {
        return self::TYPE_DEBIT;
    }

    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        return Transaction::TYPE_REFUND_REQUEST;
    }
}
