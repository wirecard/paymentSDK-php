<?php

namespace Wirecard\PaymentSdk;

/**
 * Class PayPalTransaction
 * @package Wirecard\PaymentSdk
 *
 * An immutable entity representing a payment with Paypal.
 * It does not contain logic.
 * Use the TransactionService to initiate the payment.
 */
class PayPalTransaction
{
    /**
     * @var Money
     */
    private $amount;

    /**
     * @var string
     */
    private $notificationUrl;

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * PayPalTransaction constructor.
     * @param Money $amount
     * @param string $notificationUrl
     * @param Redirect $redirect
     */
    public function __construct(Money $amount, $notificationUrl, Redirect $redirect)
    {
        $this->amount = $amount;
        $this->notificationUrl = $notificationUrl;
        $this->redirect = $redirect;
    }

    /**
     * @return Money
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getNotificationUrl()
    {
        return $this->notificationUrl;
    }

    /**
     * @return Redirect
     */
    public function getRedirect()
    {
        return $this->redirect;
    }
}
