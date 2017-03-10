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
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Mapper\RequestMapper;
use Wirecard\PaymentSdk\Mapper\ResponseMapper;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Reservable;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class TransactionService
 *
 * This service manages communication  to the Elastic Engine
 * @package Wirecard\PaymentSdk
 */
class TransactionService
{
    const APPLICATION_JSON = 'application/json';

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
     * @var \Closure
     */
    private $requestIdGenerator;

    /**
     * TransactionService constructor.
     * @param Config $config
     * @param LoggerInterface|null $logger
     * @param Client|null $httpClient
     * @param RequestMapper|null $requestMapper
     * @param ResponseMapper|null $responseMapper
     * @param \Closure|null $requestIdGenerator
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger = null,
        Client $httpClient = null,
        RequestMapper $requestMapper = null,
        ResponseMapper $responseMapper = null,
        \Closure $requestIdGenerator = null
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->requestMapper = $requestMapper;
        $this->responseMapper = $responseMapper;
        $this->requestIdGenerator = $requestIdGenerator;
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
     * @return RequestMapper
     */
    protected function getRequestMapper()
    {
        if ($this->requestMapper === null) {
            $this->requestMapper = new RequestMapper($this->config, $this->getRequestIdGenerator());
        }

        return $this->requestMapper;
    }

    /**
     * @return \Closure
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
     * @param string $xmlResponse
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
            'request_time_stamp' => gmdate('YmdHis'),
            'request_id' => call_user_func($this->getRequestIdGenerator(), 64),
            'merchant_account_id' => $this->config->get(CreditCardTransaction::NAME)->getMerchantAccountId(),
            'transaction_type' => 'tokenize',
            'requested_amount' => 0,
            'requested_amount_currency' => $this->config->getDefaultCurrency(),
            'payment_method' => 'creditcard',
        );

        $requestData['request_signature'] = hash(
            'sha256',
            trim(
                $requestData['request_time_stamp'] .
                $requestData['request_id'] .
                $requestData['merchant_account_id'] .
                $requestData['transaction_type'] .
                $requestData['requested_amount'] .
                $requestData['requested_amount_currency'] .
                $this->config->get(CreditCardTransaction::NAME)->getSecret()
            )
        );

        return json_encode($requestData);
    }

    /**
     * @param Reservable $transaction
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function reserve(Reservable $transaction)
    {
        return $this->process($transaction, Operation::RESERVE);
    }

    /**
     * @param Transaction $transaction
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function pay(Transaction $transaction)
    {
        return $this->process($transaction, Operation::PAY);
    }

    /**
     * @param Transaction $transaction
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function cancel(Transaction $transaction)
    {
        return $this->process($transaction, Operation::CANCEL);
    }

    /**
     * @param Transaction $transaction
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function credit(Transaction $transaction)
    {
        return $this->process($transaction, Operation::CREDIT);
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
     * @param string $operation
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function process(Transaction $transaction, $operation)
    {
        $transaction->setOperation($operation);

        if (null !== $transaction->getParentTransactionId()) {
            $parentTransaction = $this->getTransactionByTransactionId(
                $transaction->getParentTransactionId(),
                $transaction->getConfigKey()
            );

            if (null !== $parentTransaction && array_key_exists(Transaction::PARAM_PAYMENT, $parentTransaction)
                && array_key_exists('transaction-type', $parentTransaction[Transaction::PARAM_PAYMENT])
            ) {
                $transaction->setParentTransactionType($parentTransaction[Transaction::PARAM_PAYMENT]
                    [Transaction::PARAM_TRANSACTION_TYPE]);
            }
        }

        $requestBody = $this->getRequestMapper()->map($transaction);

        $response = $this->getHttpClient()->request(
            'POST',
            $this->config->getBaseUrl() . $transaction::ENDPOINT,
            [
                'auth' => [
                    $this->config->getHttpUser(),
                    $this->config->getHttpPassword()
                ],
                'headers' => [
                    'Content-Type' => self::APPLICATION_JSON,
                    'Accept' => 'application/xml'
                ],
                'body' => $requestBody
            ]
        );

        $data = $transaction instanceof ThreeDCreditCardTransaction ? $transaction : null;
        return $this->getResponseMapper()->map($response->getBody()->getContents(), $operation, $data);
    }

    /**
     * @param $transactionId
     * @param $paymentMethod
     * @return null|array
     */
    private function getTransactionByTransactionId($transactionId, $paymentMethod)
    {
        $response = $this->getHttpClient()->request(
            'GET',
            $this->config->getBaseUrl() .
            '/engine/rest/merchants/' .
            $this->config->get($paymentMethod)->getMerchantAccountId() .
            '/payments/' .
            $transactionId,
            [
                'auth' => [
                    $this->config->getHttpUser(),
                    $this->config->getHttpPassword()
                ],
                'headers' => [
                    'Content-Type' => self::APPLICATION_JSON,
                    'Accept' => self::APPLICATION_JSON
                ]
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array $payload
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    private function processAuthFrom3DResponse($payload)
    {
        $md = json_decode(base64_decode($payload['MD']), true);

        $transaction = new ThreeDCreditCardTransaction();
        $transaction->setParentTransactionId($md['enrollment-check-transaction-id']);
        $transaction->setPaRes($payload['PaRes']);

        return $this->process($transaction, $md['operation-type']);
    }
}
