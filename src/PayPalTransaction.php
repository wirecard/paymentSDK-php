<?php

namespace Wirecard\PaymentSdk;

/**
 * Class PayPalTransaction
 * @package Wirecard\PaymentSdk
 *
 * An entity representing a payment with Paypal.
 * It does not contain logic.
 * Use the TransactionService to initiate the payment.
 */
class PayPalTransaction
{
    /**
     * @var float
     */
    private $amount;

    /**
     * @var currency
     */
    private $currency;

    /**
     * @var string
     */
    private $notificationUrl;

    /**
     * PayPalTransaction constructor.
     * @param float $amount
     * @param currency $currency
     * @param string $notificationUrl
     */
    public function __construct($amount, currency $currency, $notificationUrl)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->notificationUrl = $notificationUrl;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getNotificationUrl()
    {
        return $this->notificationUrl;
    }

}