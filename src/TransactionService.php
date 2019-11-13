<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Client\Common\HttpMethodsClient as Client;
use Http\Message\Authentication\BasicAuth;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Payload\PayloadDataFactory;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnconfiguredPaymentMethodException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;
use Wirecard\PaymentSdk\Helper\RequestInspector;
use Wirecard\PaymentSdk\Mapper\RequestMapper;
use Wirecard\PaymentSdk\Mapper\Response\MapperFactory;
use Wirecard\PaymentSdk\Mapper\ResponseMapper;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\MaestroTransaction;
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
    const APPLICATION_XML = 'application/xml';
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
     * @var Client The HTTP clients to perform requests with
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
     * @var BasicAuth
     */
    private $basicAuth;

    /**
     * @var \Http\Message\MessageFactory
     */
    private $messageFactory;


    /**
     * TransactionService constructor.
     * @param Config $config
     * @param LoggerInterface|null $logger
     * @param RequestMapper|null $requestMapper
     * @param ResponseMapper|null $responseMapper
     * @param \Closure|null $requestIdGenerator
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger = null,
        RequestMapper $requestMapper = null,
        ResponseMapper $responseMapper = null,
        \Closure $requestIdGenerator = null
    ) {
        $this->config = $config;
        $this->logger = $logger;

        $this->messageFactory = MessageFactoryDiscovery::find();
        $this->basicAuth = new BasicAuth($this->config->getHttpUser(), $this->config->getHttpPassword());
        $this->httpClient = new Client(
            HttpClientDiscovery::find(),
            $this->messageFactory
        );

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
            'Content-Type' => self::APPLICATION_JSON,
            'Accept' => self::APPLICATION_XML
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
     * @return FailureResponse|InteractionResponse|SuccessResponse|Response
     * @throws \Http\Client\Exception
     * @throws \InvalidArgumentException
     * @throws MalformedResponseException
     * @since 4.0.0 Refactored
     */
    public function handleResponse(array $payload)
    {
        $payloadDataFactory = new PayloadDataFactory($payload, $this->config);
        $responseMapperFactory = new MapperFactory($payloadDataFactory->create());
        $responseMapper = $responseMapperFactory->create();

        return $responseMapper->map();
    }

    /**
     * @param string $language
     * @param Amount|null $amount
     * @param string|null $notificationUrl
     * @param string $paymentAction
     * @param array $additionalFields
     * @throws UnconfiguredPaymentMethodException
     * @return string
     *
     * @deprecated This method is deprecated since 2.2.0 if you still are using it please update your front-end so that
     * it uses getCreditCardUiWithData.
     */
    public function getDataForCreditCardUi(
        $language = 'en',
        Amount $amount = null,
        $notificationUrl = null,
        $paymentAction = 'authorization',
        array $additionalFields = []
    ) {
        /** @var CreditCardConfig $creditCardConfig */
        $creditCardConfig = $this->config->get(CreditCardTransaction::NAME);
        $creditCard = new CreditCardTransaction();
        $creditCard->setConfig($creditCardConfig);
        $creditCard->setAmount($amount);
        $creditCard->setNotificationUrl($notificationUrl);

        if ($additionalFields) {
            $customFields = new CustomFieldCollection();
            foreach ($additionalFields as $key => $value) {
                $customFields->add(new CustomField($key, $value));
            }
            $creditCard->setCustomFields($customFields);
        }

        return $this->getCreditCardUiWithData($creditCard, $paymentAction, $language);
    }

    /**
     * Get CreditCard Ui for Transaction
     *
     * @param Transaction $transaction
     * @param string $paymentAction
     * @param string $language
     *
     * @return string
     *
     * @since 3.7.1 Add nvp shop information to requestData
     */
    public function getCreditCardUiWithData(
        $transaction,
        $paymentAction,
        $language = 'en'
    ) {
        $config = $this->config->get($transaction::NAME);
        $merchantAccountId = $config->getMerchantAccountId();
        $secret = $config->getSecret();
        $isThreeD = false;
        $amount = $transaction->getAmount();

        if ($transaction instanceof CreditCardTransaction) {
            $isThreeD = is_null($config->getMerchantAccountId()) || ($config->getThreeDMerchantAccountId() &&
            ($transaction->isFallback() || $transaction->getThreeD())) ? true : false;
            $merchantAccountId = $isThreeD ? $config->getThreeDMerchantAccountId() : $config->getMerchantAccountId();
            $secret = $isThreeD ? $config->getThreeDSecret() : $config->getSecret();
        }

        $transactionType = 'tokenize';

        if (!is_null($amount) && $amount->getValue() > 0) {
            $transactionType = $paymentAction;
        }

        $requestData = array(
            'request_time_stamp' => gmdate('YmdHis'),
            self::REQUEST_ID => call_user_func($this->requestIdGenerator, 64),
            'transaction_type' => $transactionType,
            'merchant_account_id' => $merchantAccountId,
            'requested_amount' => is_null($amount) ? 0 : $amount->getValue(),
            'requested_amount_currency' => is_null($amount) ? 'EUR' : $amount->getCurrency(),
            'locale' => $language,
            'payment_method' => 'creditcard',
            'attempt_three_d' => $isThreeD ? true : false,
        );

        $requestData = array_merge($requestData, $this->config->getNvpShopInformation());
        $requestData = $this->requestMapper->mapSeamlessRequest($transaction, $requestData);

        $requestData['request_signature'] = $this->toSha256($requestData, $secret);

        $this->getLogger()->debug('Seamless request body: ' . json_encode($requestData));

        return json_encode($requestData);
    }

    /**
     * Get calculated signature
     *
     * @param array $fields
     * @param string $secret
     * @return string
     * @since 2.3.1
     */
    private function toSha256($fields, $secret)
    {
        $hasData = '';

        foreach ($this->getHashKeys() as $key) {
            if (isset($fields[$key])) {
                $hasData .= $fields[$key];
            }
        }
        $hasData .= $secret;
        return hash('sha256', trim($hasData));
    }

    /**
     * return order of fields for calculating the signature
     *
     * @return array
     * @since 2.3.1
     */
    private function getHashKeys()
    {
        return array(
            'request_time_stamp',
            self::REQUEST_ID,
            'merchant_account_id',
            'transaction_type',
            'requested_amount',
            'requested_amount_currency',
            'redirect_url',
            'custom_css_url',
            'ip_address'
        );
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
     * @param Transaction $transaction
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     * @throws \Http\Client\Exception
     * @throws UnsupportedOperationException
     */
    public function reserve(Transaction $transaction)
    {
        if ($transaction instanceof Reservable) {
            return $this->process($transaction, Operation::RESERVE);
        }
        throw new UnsupportedOperationException('Only reservable transactions allowed');
    }

    /**
     * @param Transaction $transaction
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     * @throws \Http\Client\Exception
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
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     * @throws \Http\Client\Exception
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
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     * @throws \Http\Client\Exception
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
     * @return string
     * @throws \Http\Client\Exception
     */
    private function sendPostRequest($endpoint, $requestBody)
    {
        $this->getLogger()->debug('Request body: ' . $requestBody);

        $requestHeader = array_merge_recursive($this->httpHeader, $this->config->getShopHeader());

        $request = $this->messageFactory->createRequest('POST', $endpoint, $requestHeader, $requestBody);
        $request = $this->basicAuth->authenticate($request);
        $response = $this->httpClient->sendRequest($request)->getBody()->getContents();
        $this->getLogger()->debug($response);

        return $response;
    }

    /**
     * @param string $endpoint
     * @param bool $acceptJson
     * @param bool $logNotFound
     * @return string|array
     * @throws \Http\Client\Exception
     * @TODO refactoring of the method is needed. For better naming and logical decisions.
     */
    private function sendGetRequest($endpoint, $acceptJson = false, $logNotFound = true)
    {
        $requestHeader = array_merge_recursive($this->httpHeader, $this->config->getShopHeader());
        $requestHeader['Accept'] = $acceptJson ? self::APPLICATION_JSON : self::APPLICATION_XML;

        $request = $this->messageFactory->createRequest('GET', $endpoint, $requestHeader);
        $request = $this->basicAuth->authenticate($request);
        $response = $this->httpClient->sendRequest($request);
        $logResponse = ($response->getStatusCode() == 404) && !$logNotFound ? false : true;
        $response = $response->getBody()->getContents();

        if ($logResponse) {
            $this->getLogger()->debug('GET response: ' . $response);
        }

        if ($acceptJson) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * @param Transaction|Reservable $transaction
     * @param string $operation
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     *
     * @throws \Http\Client\Exception
     * @since 3.7.2 Refactor credit card fallback
     */
    public function process(Transaction $transaction, $operation)
    {
        $transaction->setOperation($operation);

        if ($transaction instanceof MaestroTransaction) {
            /** @var CreditCardConfig $creditCardConfig */
            $creditCardConfig = $this->config->get(MaestroTransaction::NAME);
            $transaction->setConfig($creditCardConfig);
        } elseif ($transaction instanceof CreditCardTransaction) {
            /** @var CreditCardConfig $creditCardConfig */
            $creditCardConfig = $this->config->get(CreditCardTransaction::NAME);
            $transaction->setConfig($creditCardConfig);
        }

        if (null !== $transaction->getParentTransactionId()) {
            $parentTransaction = $this->getTransactionByTransactionId(
                $transaction->getParentTransactionId(),
                $transaction->getConfigKey()
            );

            if ($transaction instanceof CreditCardTransaction) {
                $transaction->getThreeD() ? $transaction->setThreeD(true) : $transaction->setThreeD($this->isThreeD);
            }

            if (null !== $parentTransaction) {
                if (array_key_exists(Transaction::PARAM_PAYMENT, $parentTransaction)
                    && array_key_exists('order-id', $parentTransaction[Transaction::PARAM_PAYMENT])
                ) {
                    $transaction->setOrderId($parentTransaction[Transaction::PARAM_PAYMENT]['order-id']);
                }

                if (array_key_exists(Transaction::PARAM_PAYMENT, $parentTransaction)
                    && array_key_exists('transaction-type', $parentTransaction[Transaction::PARAM_PAYMENT])
                ) {
                    $transaction->setParentTransactionType($parentTransaction[Transaction::PARAM_PAYMENT]
                    [Transaction::PARAM_TRANSACTION_TYPE]);
                }
            }
        }

        $requestBody = $this->requestMapper->map($transaction);
        $endpoint = $this->config->getBaseUrl() . $transaction->getEndpoint();
        $responseContent = $this->sendPostRequest($endpoint, $requestBody);
        $response = $this->responseMapper->map($responseContent, $transaction);

        if (null !== $response) {
            $response->setOperation($operation);
        }

        if ($transaction instanceof CreditCardTransaction
            && $response->getStatusCollection()->hasStatusCodes(['500.1072', '500.1073', '500.1074'])
            && $transaction->isFallback()
        ) {
            $response = $this->processFallback($transaction);
        }

        return $response;
    }

    /**
     * If specific status codes which indicate an error during the credit card enrollment check are found in response,
     * we do a fallback from a 3-D to an SSL credit card transaction
     *
     * @param CreditCardTransaction $transaction
     * @return Response
     *
     * @throws \Http\Client\Exception
     * @since 3.7.2 Remove $response param
     */
    private function processFallback(CreditCardTransaction $transaction)
    {
        $transaction->setThreeD(false);
        $requestBody = $this->requestMapper->map($transaction);
        $endpoint = $this->config->getBaseUrl() . $transaction->getEndpoint();
        $responseContent = $this->sendPostRequest($endpoint, $requestBody);

        return $this->responseMapper->map($responseContent, $transaction);
    }

    /**
     * We expect status code 404 for a successful authentication, otherwise the endpoint will return 401 unauthorized
     * @return boolean
     * @throws \Exception
     * @since 4.0.0 use generic Exception for Client Adaption
     */
    public function checkCredentials()
    {
        try {
            $requestHeader = array_merge_recursive($this->httpHeader, $this->config->getShopHeader());

            $request = $this->messageFactory->createRequest(
                'GET',
                $this->config->getBaseUrl() . '/engine/rest/merchants/',
                $requestHeader
            );
            $request = $this->basicAuth->authenticate($request);
            $responseCode = $this->httpClient->sendRequest($request)->getStatusCode();
        } catch (\Exception $e) {
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
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param $transactionId
     * @param $paymentMethod
     * @param bool $acceptJson
     * @return null|array|string
     * @throws \Http\Client\Exception
     */
    public function getTransactionByTransactionId($transactionId, $paymentMethod, $acceptJson = true)
    {
        $logNotFound = ($paymentMethod == CreditCardTransaction::NAME) ? false : true;
        $endpoint = $this->getTransactionEndpoint($transactionId, $paymentMethod);

        $request = $this->sendGetRequest($endpoint, $acceptJson, $logNotFound);

        //@TODO Refactor the static method
        if (!RequestInspector::isValidRequest($request) && $this->isCardTransaction($paymentMethod)) {
            $endpoint = $this->getTransactionEndpoint($transactionId, $paymentMethod, true);
            $request = $this->sendGetRequest($endpoint, $acceptJson);
            $request !== null ? $this->isThreeD = true : $this->isThreeD = false;
        }

        return $request;
    }

    /**
     * Get the REST API endpoint for a transaction
     *
     * @param string $paymentMethod
     * @param string $transactionId
     * @param bool $isThreeD
     * @return string
     * @since 4.0.0
     */
    private function getTransactionEndpoint($transactionId, $paymentMethod, $isThreeD = false)
    {
        $merchantAccountId = $isThreeD
            ? $this->config->get($paymentMethod)->getThreeDMerchantAccountId()
            : $this->config->get($paymentMethod)->getMerchantAccountId();

        return sprintf(
            '%s/engine/rest/merchants/%s/payments/%s',
            $this->config->getBaseUrl(),
            $merchantAccountId,
            $transactionId
        );
    }

    /**
     * Check if the current payment method is card-based
     *
     * @param $paymentMethod
     * @return bool
     * @since 4.0.0
     */
    private function isCardTransaction($paymentMethod)
    {
        return in_array(
            $paymentMethod,
            [CreditCardTransaction::NAME, MaestroTransaction::NAME]
        );
    }


    /**
     * @param $requestId
     * @param $paymentMethod
     * @param bool $acceptJson
     * @return null|array|string
     * @throws \Http\Client\Exception
     * @since 3.3.0
     */
    public function getTransactionByRequestId($requestId, $paymentMethod, $acceptJson = true)
    {
        //if in the request we get a 404 and this parameter is set to false we will write a log.
        $logNotFound = ($paymentMethod == CreditCardTransaction::NAME) ? false : true;
        $endpoint =
            $this->config->getBaseUrl() .
            '/engine/rest/merchants/' .
            $this->config->get($paymentMethod)->getMerchantAccountId();

        if ($paymentMethod === IdealTransaction::NAME) {
            $endpoint .= '/payments/search?payment.request-id=' . $requestId;
        } else {
            $endpoint .= '/payments/?request_id=' . $requestId;
        }

        return $this->sendGetRequest($endpoint, $acceptJson, $logNotFound);
    }

    /**
     * @param array $payload
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     * @throws \Http\Client\Exception
     * @since 2.1.0
     */
    public function processJsResponse($payload)
    {
        $this->getLogger()->debug('GET seamless response: ' . json_encode($payload));
        return $this->handleResponse($payload);
    }

    /**
     * Recursively search for a parent transaction until it finds no parent-transaction-id anymore. Aggregate all of the
     * results received in the group transaction and return them in ascending order by date.
     * @param $transactionId
     * @param $paymentMethod
     * @return array
     * @throws \Http\Client\Exception
     * @since 3.1.0
     */
    public function getGroupOfTransactions($transactionId, $paymentMethod)
    {
        $transaction = $this->getTransactionByTransactionId($transactionId, $paymentMethod);
        if (isset($transaction['payment'])) {
            $transaction = $transaction['payment'];
        }
        if (isset($transaction['parent-transaction-id'])) {
            return $this->getGroupOfTransactions($transaction['parent-transaction-id'], $paymentMethod);
        } else {
            $endpoint =
                $this->config->getBaseUrl() .
                '/engine/rest/merchants/' .
                $transaction['merchant-account-id']['value'] .
                '/payments/?group_transaction_id=' . $transactionId;
            $xml = (array)simplexml_load_string($this->sendGetRequest($endpoint));
            $ret = [];
            if (isset($xml['payment'])) {
                foreach ($xml['payment'] as $response) {
                    $ret[] = $response;
                }

                usort($ret, function ($item1, $item2) {
                    $date1 = new \DateTime($item1->{'completion-time-stamp'});
                    $date2 = new \DateTime($item2->{'completion-time-stamp'});
                    return $date1 == $date2 ? 0 : ($date1 < $date2 ? -1 : 1);
                });
            }

            return $ret;
        }
    }

    /**
     * Access to the configuration object which was set in constructor
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
