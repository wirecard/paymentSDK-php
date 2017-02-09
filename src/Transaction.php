<?php

namespace Wirecard\PaymentSdk;

/**
 * Class Transaction
 * @package Wirecard\PaymentSdk
 *
 * An immutable entity representing a payment
 * without any payment method specific properties.
 *
 * It does not contain logic.
 * Use the TransactionService to initiate the payment.
 */
abstract class Transaction
{
    /**
     * @var Money
     */
    private $amount;

    /**
     * Transaction constructor.
     * @param Money $amount
     */
    public function __construct(Money $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return Money
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
