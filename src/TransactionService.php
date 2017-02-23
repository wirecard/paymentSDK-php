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

namespace Wirecard\PaymentSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Mapper\RequestMapper;
use Wirecard\PaymentSdk\Mapper\ResponseMapper;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\FollowupTransaction;
use Wirecard\PaymentSdk\Transaction\InitialTransaction;
use Wirecard\PaymentSdk\Transaction\ThreeDAuthorizationTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class TransactionService
 *
 * This service manages communication  to the elastic engine
 * @package Wirecard\PaymentSdk
 */
class TransactionService
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var RequestMapper
     */
    private $requestMapper;

    /**
     * @var ResponseMapper
     */
    private $responseMapper;

    /**
     * @var callable
     */
    private $requestIdGenerator;

    /**
     * TransactionService constructor.
     * @param Config $config
     * @param LoggerInterface|null $logger
     * @param Client|null $httpClient
     * @param RequestMapper|null $requestMapper
     * @param ResponseMapper|null $responseMapper
     * @param callable|null $requestIdGenerator
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger = null,
        Client $httpClient = null,
        RequestMapper $requestMapper = null,
        ResponseMapper $responseMapper = null,
        callable $requestIdGenerator = null
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->requestMapper = $requestMapper;
        $this->responseMapper = $responseMapper;
        $this->requestIdGenerator = $requestIdGenerator;
    }

    /**
     * @param InitialTransaction $transaction
     * @throws RequestException|MalformedResponseException|\RuntimeException
     * @return InteractionResponse|FailureResponse
     */
    public function pay(InitialTransaction $transaction)
    {
        return $this->process($transaction);
    }

    /**
     * @param FollowupTransaction $transaction
     * @return FailureResponse|InteractionResponse|SuccessResponse
     * @throws \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function cancel(FollowupTransaction $transaction)
    {
        return $this->process($transaction);
    }

    /**
     * @param InitialTransaction $transaction
     * @throws RequestException|MalformedResponseException|\RuntimeException
     * @return FailureResponse|InteractionResponse|SuccessResponse
     */
    public function reserve(InitialTransaction $transaction)
    {
        return $this->process($transaction);
    }

    /**
     * @return Client
     */
    protected function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client(['http_errors' => false]);
        }

        return $this->httpClient;
    }

    /**
     * @return Config
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @return RequestMapper
     */
    protected function getRequestMapper()
    {
        if ($this->requestMapper === null) {
            $this->requestMapper = new RequestMapper($this->getConfig(), $this->getRequestIdGenerator());
        }

        return $this->requestMapper;
    }

    /**
     * @return callable
     */
    public function getRequestIdGenerator()
    {
        if ($this->requestIdGenerator === null) {
            $this->requestIdGenerator = function ($length = 32) {
                return substr(bin2hex(openssl_random_pseudo_bytes($length)), 0, $length);
            };
        }

        return $this->requestIdGenerator;
    }

    /**
     * @return ResponseMapper
     */
    protected function getResponseMapper()
    {
        if ($this->responseMapper === null) {
            $this->responseMapper = new ResponseMapper();
        }

        return $this->responseMapper;
    }

    /**
     * @param $xmlResponse
     * @return FailureResponse|InteractionResponse|SuccessResponse|Response
     * @throws \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function handleNotification($xmlResponse)
    {
        return $this->getResponseMapper()->map($xmlResponse);
    }

    /**
     * @param array $payload
     * @return FailureResponse|InteractionResponse|SuccessResponse|Response
     * @throws MalformedResponseException
     */
    public function handleResponse(array $payload)
    {
        if (array_key_exists('MD', $payload) && array_key_exists('PaRes', $payload)) {
            return $this->processAuthFrom3DResponse($payload);
        }

        if (array_key_exists('eppresponse', $payload)) {
            return $this->getResponseMapper()->map($payload['eppresponse']);
        } else {
            throw new MalformedResponseException('Missing response in payload');
        }
    }

    /**
     * @return string
     */
    public function getDataForCreditCardUi()
    {
        $requestData = array(
            'request_time_stamp'        => gmdate('YmdHis'),
            'request_id'                => call_user_func($this->getRequestIdGenerator(), 64),
            'merchant_account_id'       => $this->getConfig()->getMerchantAccountId(),
            'transaction_type'          => 'tokenize',
            'requested_amount'          => 0,
            'requested_amount_currency' => $this->getConfig()->getDefaultCurrency(),
            'payment_method'            => 'creditcard',
        );

        $requestData['request_signature'] = hash('sha256', trim(
            $requestData['request_time_stamp'] .
            $requestData['request_id'] .
            $requestData['merchant_account_id'] .
            $requestData['transaction_type'] .
            $requestData['requested_amount'] .
            $requestData['requested_amount_currency'] .
            $this->getConfig()->getSecretKey()
        ));

        return json_encode($requestData);
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = new Logger('wirecard_payment_sdk');
            $this->logger->pushHandler(new ErrorLogHandler());
        }

        return $this->logger;
    }

    /**
     * @param Transaction $transaction
     * @return FailureResponse|InteractionResponse|SuccessResponse|Response
     * @throws RequestException|MalformedResponseException|\RuntimeException
     */
    private function process(Transaction $transaction)
    {
        $requestBody = $this->getRequestMapper()->map($transaction);
        $response = $this->getHttpClient()->request(
            'POST',
            $this->getConfig()->getUrl(),
            [
                'auth' => [
                    $this->getConfig()->getHttpUser(),
                    $this->getConfig()->getHttpPassword()
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/xml'
                ],
                'body' => $requestBody
            ]
        );
        return $this->getResponseMapper()->map($response->getBody()->getContents(), $transaction);
    }

    private function processAuthFrom3DResponse($payload)
    {
        $refTransaction = new ThreeDAuthorizationTransaction($payload);
        return $this->process($refTransaction);
    }
}
