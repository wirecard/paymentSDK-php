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

namespace Wirecard\PaymentSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException;
use Wirecard\PaymentSdk\Mapper\RequestMapper;
use Wirecard\PaymentSdk\Mapper\ResponseMapper;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\CreditCardMotoTransaction;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\Reservable;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Transaction\UpiTransaction;

/**
 * Class TransactionService
 *
 * This service manages communication to the Wirecard REST interface
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
     * @var boolean
     */
    private $isThreeD;


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
        $this->responseMapper = $responseMapper !== null ? $responseMapper : new ResponseMapper($this->config);

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
     * @throws \InvalidArgumentException
     * @throws \Wirecard\PaymentSdk\Exception\MalformedResponseException
     */
    public function handleNotification($xmlResponse)
    {
        return $this->responseMapper->mapInclSignature($xmlResponse);
    }

    /**
     * @param array $payload
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
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
            $data = $this->responseMapper->mapInclSignature($payload['eppresponse']);
        }

        // RatePAY installment
        if (null === $data &&
            array_key_exists('base64payload', $payload) &&
            array_key_exists('psp_name', $payload)
        ) {
            $data = $this->responseMapper->mapInclSignature($payload['base64payload']);
        }

        // Synchronous payment methods
        if (null === $data && array_key_exists('sync_response', $payload)) {
            $data = $this->responseMapper->mapInclSignature($payload['sync_response']);
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
    public function getDataForCreditCardUi($language = 'en')
    {
        $requestData = array(
            'request_time_stamp' => gmdate('YmdHis'),
            self::REQUEST_ID => call_user_func($this->requestIdGenerator, 64),
            'transaction_type' => 'authorization-only',
            'merchant_account_id' => $this->config->get(CreditCardTransaction::NAME)->getMerchantAccountId(),
            'requested_amount' => 0,
            'requested_amount_currency' => $this->config->getDefaultCurrency(),
            'locale' => $language,
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
     * @throws UnconfiguredPaymentMethodException
     * @return string
     */
    public function getDataForCreditCardMotoUi($language = 'en')
    {
        $requestData = array(
            'request_time_stamp' => gmdate('YmdHis'),
            self::REQUEST_ID => call_user_func($this->requestIdGenerator, 64),
            'transaction_type' => 'authorization-only',
            'merchant_account_id' => $this->config->get(CreditCardMotoTransaction::NAME)->getMerchantAccountId(),
            'requested_amount' => 0,
            'requested_amount_currency' => $this->config->getDefaultCurrency(),
            'locale' => $language,
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
                $this->config->get(CreditCardMotoTransaction::NAME)->getSecret()
            )
        );

        return json_encode($requestData);
    }

    /**
     * @throws UnconfiguredPaymentMethodException
     * @return string
     */
    public function getDataForUpiUi($language = 'en')
    {
        $requestData = array(
            'request_time_stamp' => gmdate('YmdHis'),
            self::REQUEST_ID => call_user_func($this->requestIdGenerator, 64),
            'transaction_type' => 'authorization-only',
            'merchant_account_id' => $this->config->get(UpiTransaction::NAME)->getMerchantAccountId(),
            'requested_amount' => 0,
            'requested_amount_currency' => $this->config->getDefaultCurrency(),
            'locale' => $language,
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
                $this->config->get(UpiTransaction::NAME)->getSecret()
            )
        );

        return json_encode($requestData);
    }

    /**
     * @return string
     */
    public function getRatePayInvoiceDeviceIdent()
    {
        $timestamp = microtime();
        $customerId = $this->config->get(RatepayInvoiceTransaction::NAME)->getMerchantAccountId();
        $deviceIdentToken = md5($customerId . "_" . $timestamp);

        return $deviceIdentToken;
    }

    /**
     * @return string
     */
    public function getRatePayInstallmentDeviceIdent()
    {
        $timestamp = microtime();
        $customerId = $this->config->get(RatepayInstallmentTransaction::NAME)->getMerchantAccountId();
        $deviceIdentToken = md5($customerId . "_" . $timestamp);

        return $deviceIdentToken;
    }

    /**
     * @param $deviceIdentToken
     * @return string
     */
    public function getRatePayScript($deviceIdentToken)
    {
        $script =
          "<script language='JavaScript'>
          var di = {t:'$deviceIdentToken',v:'WDWL',l:'Checkout'};
          </script>
          <script type='text/javascript' src='//d.ratepay.com/WDWL/di.js'>
          </script>
          <noscript>
          <link rel='stylesheet' type='text/css' href='//d.ratepay.com/di.css?t=$deviceIdentToken&v=WDWL&l=Checkout'>
          </noscript>
          <object type='application/x-shockwave-flash' data='//d.ratepay.com/WDWL/c.swf' width='0' height='0'>
          <param name='movie' value='//d.ratepay.com/WDWL/c.swf' />
          <param name='flashvars' value='t=$deviceIdentToken&v=WDWL'/><param name='AllowScriptAccess' value='always'/>
          </object>";

        return $script;
    }

    /**
     * @param Reservable $transaction
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws MandatoryFieldMissingException
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
     * @throws \InvalidArgumentException
     * @throws MandatoryFieldMissingException
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function pay(Transaction $transaction)
    {
        return $this->process($transaction, Operation::PAY);
    }

    /**
     * If a failureResponse returns from the cancel process call
     * with a specific status code which declares that the credit card amount has already been settled,
     * we try a refund process call.
     *
     * @param Transaction $transaction
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws MandatoryFieldMissingException
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function cancel(Transaction $transaction)
    {
        $cancelResult = $this->process($transaction, Operation::CANCEL);

        if ($transaction instanceof CreditCardTransaction
            && $cancelResult->getStatusCollection()->hasStatusCodes(['500.1057'])
        ) {
            return $this->process($transaction, Operation::REFUND);
        }

        return $cancelResult;
    }

    /**
     * @param Transaction $transaction
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws MandatoryFieldMissingException
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

        $requestHeader = array_merge_recursive($this->httpHeader, $this->config->getShopHeader());
        $requestHeader['body'] = $requestBody;

        $response = $this->httpClient
            ->request('POST', $endpoint, $requestHeader)
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
        $requestHeader = array_merge_recursive($this->httpHeader, $this->config->getShopHeader());
        $requestHeader['headers']['Accept'] = $acceptJson ? self::APPLICATION_JSON : 'application/xml';

        $response = $this->httpClient
            ->request('GET', $endpoint, $requestHeader)
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
     * @throws MandatoryFieldMissingException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    public function process(Transaction $transaction, $operation)
    {
        $transaction->setOperation($operation);

        if ($transaction instanceof CreditCardTransaction) {
            $transaction->setConfig($this->config->get(CreditCardTransaction::NAME));
        }
        if (null !== $transaction->getParentTransactionId()) {
            $parentTransaction = $this->getTransactionByTransactionId(
                $transaction->getParentTransactionId(),
                $transaction->getConfigKey()
            );
            if ($transaction instanceof CreditCardTransaction) {
                $transaction->getThreeD() ? $transaction->setThreeD(true) : $transaction->setThreeD($this->isThreeD);
            }
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
        $response = $this->responseMapper->map($responseContent, $transaction);

        if (null !== $response) {
            $response->setOperation($operation);
        }

        if ($transaction instanceof CreditCardTransaction && $transaction->isFallback()) {
            return $this->processFallback($transaction, $response);
        }

        return $response;
    }

    /**
     * If specific status codes which indicate an error during the credit card enrollment check are found in response,
     * we do a fallback from a 3-D to an SSL credit card transaction
     *
     * @param CreditCardTransaction $transaction
     * @param Response $response
     * @throws UnconfiguredPaymentMethodException
     * @throws MandatoryFieldMissingException
     * @throws \RuntimeException
     * @throws MalformedResponseException
     * @throws \InvalidArgumentException
     * @return Response
     */
    private function processFallback(CreditCardTransaction $transaction, Response $response)
    {
        if (!$response->getStatusCollection()->hasStatusCodes(['500.1072', '500.1073', '500.1074'])) {
            return $response;
        }

        $transaction->setThreeD(false);
        $requestBody = $this->requestMapper->map($transaction);
        $endpoint = $this->config->getBaseUrl() . $transaction->getEndpoint();
        $responseContent = $this->sendPostRequest($endpoint, $requestBody);

        return $this->responseMapper->map($responseContent, $transaction);
    }

    /**
     * We expect status code 404 for a successful authentication, otherwise the endpoint will return 401 unauthorized
     * @return boolean
     */
    public function checkCredentials()
    {
        try {
            $requestHeader = array_merge_recursive($this->httpHeader, $this->config->getShopHeader());

            $responseCode = $this->httpClient
                ->request('GET', $this->config->getBaseUrl() . '/engine/rest/merchants/', $requestHeader)
                ->getStatusCode();
        } catch (TransferException $e) {
            $this->getLogger()->debug('Check credentials: Error - ' . $e->getMessage());
            return false;
        }

        if ($responseCode === 404) {
            $this->getLogger()->debug('Check credentials: Valid');
            return true;
        }

        $this->getLogger()->debug('Check credentials: Invalid - Received status code: ' . $responseCode);
        return false;
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

        $request = $this->sendGetRequest($endpoint, true);
        if ($request == null && $paymentMethod == CreditCardTransaction::NAME) {
            $endpoint =
                $this->config->getBaseUrl() .
                '/engine/rest/merchants/' .
                $this->config->get($paymentMethod)->getThreeDMerchantAccountId() .
                '/payments/' . $transactionId;
            $request = $this->sendGetRequest($endpoint, true);
            $request !== null ? $this->isThreeD = true : $this->isThreeD = false;
        }

        return $request;
    }

    /**
     * @param array $payload
     * @throws MalformedResponseException
     * @throws UnconfiguredPaymentMethodException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws MandatoryFieldMissingException
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     */
    private function processAuthFrom3DResponse($payload)
    {
        $md = json_decode(base64_decode($payload['MD']), true);

        $transaction = new CreditCardTransaction();
        $transaction->setParentTransactionId($md['enrollment-check-transaction-id']);
        $transaction->setPaRes($payload['PaRes']);
        $transaction->setThreeD(true);

        return $this->process($transaction, $md['operation-type']);
    }

    /**
     * @param array $payload
     * @throws UnconfiguredPaymentMethodException
     * @throws MalformedResponseException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
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
