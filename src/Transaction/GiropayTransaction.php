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

use Wirecard\PaymentSdk\Entity\BankAccount;

/**
 * Class GiropayTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class GiropayTransaction extends Transaction implements Reservable
{
    const NAME = 'giropay';

    /**
     * @var string
     */
    private $orderDetail;

    /**
     * @var string
     */
    private $bankData;

    /**
     * @param $orderDetail
     * @return $this
     */
    public function setOrderDetail($orderDetail)
    {
        $this->orderDetail = $orderDetail;
        return $this;
    }

    /**
     * @param BankAccount $bankAccount
     * @return $this
     */
    public function setBankAccount(BankAccount $bankAccount)
    {

        $this->bankData = $bankAccount->mappedProperties();
        return $this;
    }

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $data = [];

        if (null !== $this->orderDetail) {
            $data['order-detail'] = $this->orderDetail;
        }

        if (null !== $this->bankData) {
            foreach ($this->bankData as $key => $val) {
                $data['bank-account'][$key] = $val;
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        return self::TYPE_DEBIT;
    }

    /**
     * @return string
     */

    /**
     * @return string
     */


    /**
     * @return string
     */
    public function getEndpoint()
    {
        if ($this->parentTransactionId) {
            return self::ENDPOINT_PAYMENTS;
        }
        return self::ENDPOINT_PAYMENT_METHODS;
    }
}
