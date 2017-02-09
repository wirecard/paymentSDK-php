<?php

namespace Wirecard\PaymentSdk;

/**
 * Class CreditCardTransaction
 * @package Wirecard\PaymentSdk
 *
 * An immutable entity representing a payment with credit card.
 * Use it for SSL payments.
 * For the 3D payments use the specific subclass.
 */
class CreditCardTransaction extends Transaction
{
    private $transactionId;

    /**
     * CreditCardTransaction constructor.
     * @param Money $money
     * @param $transactionId
     */
    public function __construct($money, $transactionId)
    {
        parent::__construct($money);
        $this->transactionId = $transactionId;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
