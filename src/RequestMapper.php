<?php

namespace Wirecard\PaymentSdk;

class RequestMapper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RequestIdGenerator
     */
    private $requestIdGenerator;

    /**
     * RequestMapper constructor.
     * @param Config $config
     * @param RequestIdGenerator $requestIdGenerator
     */
    public function __construct(Config $config, RequestIdGenerator $requestIdGenerator)
    {
        $this->config = $config;
        $this->requestIdGenerator = $requestIdGenerator;
    }

    /**
     * @param PayPalTransaction $transaction
     * @return The transaction in JSON format.
     */
    public function map(PayPalTransaction $transaction)
    {
        $onlyPaymentMethod = ['payment-method' => [['name' => 'paypal']]];
        $amount = [
            'currency' => $transaction->getAmount()->getCurrency(),
            'value' => $transaction->getAmount()->getAmount()
        ];
        $requestId = $this->requestIdGenerator->generate();

        $result = ['payment' => [
            'merchant-account-id' => ['value' => $this->config->getMerchantAccountId()],
            'request-id' => $requestId,
            'transaction-type' => 'debit',
            'requested-amount' => $amount,
            'payment-methods' => $onlyPaymentMethod,
            'cancel-redirect-url' => $transaction->getRedirect()->getCancelUrl(),
            'success-redirect-url' => $transaction->getRedirect()->getSuccessUrl()
        ]];
        return json_encode($result);
    }
}
