<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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
