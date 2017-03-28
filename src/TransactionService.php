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
use Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException;
use Wirecard\PaymentSdk\Mapper\RequestMapper;
use Wirecard\PaymentSdk\Mapper\ResponseMapper;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
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
    const REQUEST_ID = 'request_id';

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
     * @var array
     */
    private $httpHeader;


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
        $this->httpClient = $httpClient !== null ? $httpClient : new Client(['http_errors' => false]);

        if ($requestIdGenerator !== null) {
            $this->requestIdGenerator = $requestIdGenerator;
        } else {
            $this->requestIdGenerator = function ($length = 32) {
                return substr(bin2hex(openssl_random_pseudo_bytes($length)), 0, $length);
            };
        }

        $this->requestMapper =
            $requestMapper !== null ? $requestMapper : new RequestMapper($this->config, $this->requestIdGenerator);
        $this->responseMapper = $responseMapper !== null ? $responseMapper : new ResponseMapper();

        $this->httpHeader = array(
            'auth' => [
                $this->config->getHttpUser(),
                $this->config->getHttpPassword()
            ],
            'headers' => [
                'Content-Type' => self::APPLICATION_JSON,
                'Accept' => 'application/xml'
            ]
        );
    }

    /**
     * @param string $xmlResponse
     * @return FailureResponse|InteractionResponse|SuccessResponse|Response
     * @throws \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function handleNotification($xmlResponse)
    {
        return $this->responseMapper->map($xmlResponse);
    }

    /**
     * @param array $payload
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @return FailureResponse|InteractionResponse|SuccessResponse|Response
     */
    public function handleResponse(array $payload)
    {
        $data = null;

        // 3-D Secure PaRes
        if (array_key_exists('MD', $payload) && array_key_exists('PaRes', $payload)) {
            $data = $this->processAuthFrom3DResponse($payload);
        }

        // iDEAL
        if (null === $data &&
            array_key_exists('ec', $payload) &&
            array_key_exists('trxid', $payload) &&
            array_key_exists(self::REQUEST_ID, $payload)
        ) {
            $data = $this->processFromIdealResponse($payload);
        }

        // PayPal
        if (null === $data && array_key_exists('eppresponse', $payload)) {
            $data = $this->responseMapper->map($payload['eppresponse']);
        }

        // RatePAY installment
        if (null === $data &&
            array_key_exists('base64payload', $payload) &&
            array_key_exists('psp_name', $payload)
        ) {
            $data = $this->responseMapper->map($payload['base64payload']);
        }

        if ($data instanceof Response) {
            return $data;
        }

        throw new MalformedResponseException('Missing response in payload.');
    }

    /**
     * @throws UnconfiguredPaymentMethodException
     * @return string
     */
    public function getDataForCreditCardUi()
    {
        $requestData = array(
            'request_time_stamp' => gmdate('YmdHis'),
            self::REQUEST_ID => call_user_func($this->requestIdGenerator, 64),
            'transaction_type' => 'authorization-only',
            'merchant_account_id' => $this->config->get(CreditCardTransaction::NAME)->getMerchantAccountId(),
            'requested_amount' => 0,
            'requested_amount_currency' => $this->config->getDefaultCurrency(),
            'payment_method' => 'creditcard',
        );

        $requestData['request_signature'] = hash(
            'sha256',
            trim(
                $requestData['request_time_stamp'] .
                $requestData[self::REQUEST_ID] .
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
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function reserve(Reservable $transaction)
    {
        return $this->process($transaction, Operation::RESERVE);
    }

    /**
     * @param Transaction $transaction
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function pay(Transaction $transaction)
    {
        return $this->process($transaction, Operation::PAY);
    }

    /**
     * @param Transaction $transaction
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function cancel(Transaction $transaction)
    {
        return $this->process($transaction, Operation::CANCEL);
    }

    /**
     * @param Transaction $transaction
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
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
            $errorHandler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $this->config->getLogLevel());
            $this->logger->pushHandler($errorHandler);
        }

        return $this->logger;
    }

    /**
     * @param $endpoint
     * @param string $requestBody
     * @throws \RuntimeException
     * @return string
     */
    private function sendPostRequest($endpoint, $requestBody)
    {
        $this->getLogger()->debug('Request body: ' . $requestBody);

        $request = $this->httpHeader;
        $request['body'] = $requestBody;

        $response = $this->httpClient
            ->request('POST', $endpoint, $request)
            ->getBody()->getContents();

        $this->getLogger()->debug($response);

        return $response;
    }

    /**
     * @param $endpoint
     * @param bool $acceptJson
     * @throws \RuntimeException
     * @return string|array
     */
    private function sendGetRequest($endpoint, $acceptJson = false)
    {
        $request = $this->httpHeader;
        $request['headers']['Accept'] = $acceptJson ? self::APPLICATION_JSON : 'application/xml';

        $response = $this->httpClient
            ->request('GET', $endpoint, $request)
            ->getBody()->getContents();

        $this->getLogger()->debug('GET response: ' . $response);

        if ($acceptJson) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * @param Transaction|Reservable $transaction
     * @param string $operation
     * @throws UnconfiguredPaymentMethodException
     * @throws MalformedResponseException
     * @throws \RuntimeException
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

        $requestBody = $this->requestMapper->map($transaction);
        $endpoint = $this->config->getBaseUrl() . $transaction->getEndpoint();
        $responseContent = $this->sendPostRequest($endpoint, $requestBody);

        $data = $transaction instanceof ThreeDCreditCardTransaction ? $transaction : null;
        return $this->responseMapper->map($responseContent, $data);
    }

    /**
     * @param $transactionId
     * @param $paymentMethod
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @return null|array
     */
    private function getTransactionByTransactionId($transactionId, $paymentMethod)
    {
        $endpoint =
            $this->config->getBaseUrl() .
            '/engine/rest/merchants/' .
            $this->config->get($paymentMethod)->getMerchantAccountId() .
            '/payments/' . $transactionId;

        return $this->sendGetRequest($endpoint, true);
    }

    /**
     * @param array $payload
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
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

    /**
     * @param array $payload
     * @throws UnconfiguredPaymentMethodException
     * @throws MalformedResponseException
     * @throws \RuntimeException
     * @return Response
     */
    private function processFromIdealResponse($payload)
    {
        $endpoint =
            $this->config->getBaseUrl() . '/engine/rest/merchants/' .
            $this->config->get(IdealTransaction::NAME)->getMerchantAccountId() .
            '/payments/search?payment.request-id=' . $payload[self::REQUEST_ID];
        $transaction = $this->sendGetRequest($endpoint);

        return $this->responseMapper->map($transaction);
    }
}
