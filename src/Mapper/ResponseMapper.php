<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Mapper;

use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SimpleXMLElement;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Constant\SeamlessFields;
use Wirecard\PaymentSdk\Constant\StatusFields;
use Wirecard\PaymentSdk\Constant\ResponseMappingXmlFields;
use Wirecard\PaymentSdk\Entity\FormFieldMap;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\PendingResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class ResponseMapper
 * @package Wirecard\PaymentSdk\Mapper
 */
class ResponseMapper
{
    const FORM_FIELD_TERM_URL = 'TermUrl';
    const FORM_FIELD_MD = 'MD';
    const FORM_FIELD_PAREQ = 'PaReq';
    const FORM_FIELD_SYNC_RESPONSE = 'sync_response';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    protected $xmlResponse;

    /**
     * @var SimpleXMLElement
     */
    protected $simpleXml;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * ResponseMapper constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $response
     * @throws \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @throws \InvalidArgumentException
     * @return Response
     */
    public function mapInclSignature($response)
    {
        $result = $this->map($response);
        $validSignature = $this->validateSignature($this->xmlResponse);
        $result->setValidSignature($validSignature);

        return $result;
    }

    /**
     * map the xml Response from Wirecard's Payment Processing Gateway to ResponseObjects
     *
     * @param string $response
     * @param Transaction $transaction
     * @throws \InvalidArgumentException
     * @throws MalformedResponseException
     * @return Response
     */
    public function map($response, Transaction $transaction = null)
    {
        $this->transaction = $transaction;

        // If the response is encoded, we need to first decode it.
        $decodedResponse = base64_decode($response);
        $this->xmlResponse = (base64_encode($decodedResponse) === $response) ? $decodedResponse : $response;
        //we need to use internal_errors, because we don't want to throw errors on invalid xml responses
        $oldErrorHandling = libxml_use_internal_errors(true);
        $this->simpleXml = simplexml_load_string($this->xmlResponse);
        //reset to old value after string is loaded
        libxml_use_internal_errors($oldErrorHandling);

        if (!$this->simpleXml instanceof \SimpleXMLElement) {
            throw new MalformedResponseException('Response is not a valid xml string.');
        }

        if (isset($this->simpleXml->{'transaction-state'})) {
            $state = (string)$this->simpleXml->{'transaction-state'};
        } else {
            throw new MalformedResponseException('Missing transaction-state in response.');
        }

        switch ($state) {
            case 'success':
                return $this->mapSuccessResponse();
            case 'in-progress':
                return new PendingResponse($this->simpleXml);
            default:
                return new FailureResponse($this->simpleXml);
        }
    }

    /**
     * @param string $xmlResponse
     * @return boolean
     */
    private function validateSignature($xmlResponse)
    {
        $result = true;

        $domResponse = new \DOMDocument();
        $domResponse->loadXML($xmlResponse);

        $xmlSecDSig = new XMLSecurityDSig();
        $dSig = $xmlSecDSig->locateSignature($domResponse);

        if (!$dSig) {
            return false;
        }

        $xmlSecDSig->canonicalizeSignedInfo();
        $key = $xmlSecDSig->locateKey();

        if (!$key) {
            return false;
        }

        try {
            XMLSecEnc::staticLocateKeyInfo($key, $dSig);

            if (null !== $this->config->getPublicKey()) {
                $key->loadKey($this->config->getPublicKey());
            }

            if (1 !== $xmlSecDSig->verify($key)) {
                $result = false;
            }

            $xmlSecDSig->validateReference();
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @throws MalformedResponseException
     * @return mixed
     */
    private function getPaymentMethod()
    {
        if (isset($this->simpleXml->{'payment-methods'})) {
            $paymentMethods = $this->simpleXml->{'payment-methods'};
        } elseif (isset($this->simpleXml->{'card-token'})) {
            return new \SimpleXMLElement('<payment-methods>
                                              <payment-method name="creditcard"></payment-method>
                                          </payment-methods>');
        } else {
            throw new MalformedResponseException('Missing payment methods in response.');
        }

        if (isset($paymentMethods->{'payment-method'})) {
            $paymentMethod = $paymentMethods->{'payment-method'};
        } else {
            throw new MalformedResponseException('Payment methods is empty in response.');
        }

        if (count($paymentMethod) === 1) {
            return $paymentMethod[0];
        }

        throw new MalformedResponseException('More payment methods in response.');
    }

    /**
     * @throws MalformedResponseException
     * @throws \InvalidArgumentException
     * @return FormInteractionResponse
     *
     * @deprecated This method is deprecated since 2.1.0 if you still are using it please update your front-end so that
     * it uses mapSeamlessResponse.
     */
    private function mapThreeDResponse()
    {
        if (!($this->transaction instanceof CreditCardTransaction)) {
            throw new \InvalidArgumentException('Trying to create a 3D response from a non-3D transaction.');
        }
        if (!isset($this->simpleXml->{'three-d'})) {
            throw new MalformedResponseException('Missing three-d element in enrollment-check response.');
        }

        $threeD = $this->simpleXml->{'three-d'};

        if (!isset($threeD->{'acs-url'})) {
            throw new MalformedResponseException('Missing acs redirect url in enrollment-check response.');
        }

        $redirectUrl = (string)$threeD->{'acs-url'};

        $response = new FormInteractionResponse($this->simpleXml, $redirectUrl);

        if (!isset($threeD->{'pareq'})) {
            throw new MalformedResponseException('Missing pareq in enrollment-check response.');
        }

        $fields = new FormFieldMap();
        $fields->add(self::FORM_FIELD_TERM_URL, $this->transaction->getTermUrl());
        $fields->add(self::FORM_FIELD_PAREQ, (string)$threeD->{'pareq'});
        $fields->add(
            self::FORM_FIELD_MD,
            http_build_query([
                SeamlessFields::MERCHANT_ACCOUNT_ID => $this->simpleXml->{'merchant-account-id'},
                SeamlessFields::TRANSACTION_TYPE => $this->transaction->retrieveOperationType(),
                SeamlessFields::TRANSACTION_ID => $response->getTransactionId(),
            ])
        );

        $response->setFormFields($fields);

        return $response;
    }

    /**
     * @throws MalformedResponseException
     * @return FormInteractionResponse
     */
    private function redirectToSuccessUrlWithPayload()
    {
        $payload = base64_encode($this->simpleXml->asXML());

        $formFields = new FormFieldMap();
        $formFields->add(self::FORM_FIELD_SYNC_RESPONSE, $payload);

        $response = new FormInteractionResponse($this->simpleXml, $this->transaction->getSuccessUrl());
        $response->setFormFields($formFields);

        return $response;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws MalformedResponseException
     * @return FormInteractionResponse|InteractionResponse|SuccessResponse
     */
    private function mapSuccessResponse()
    {
        if ((string)$this->simpleXml->{'transaction-type'} === CreditCardTransaction::TYPE_CHECK_ENROLLMENT) {
            return $this->mapThreeDResponse();
        }

        $paymentMethod = $this->getPaymentMethod();

        if (isset($paymentMethod['url'])) {
            return new InteractionResponse($this->simpleXml, (string)$paymentMethod['url']);
        }

        if (null !== $this->transaction && null !== $this->transaction->getSuccessUrl()) {
            return $this->redirectToSuccessUrlWithPayload();
        }

        return new SuccessResponse($this->simpleXml);
    }

    /**
     * @param $payload
     * @param string $url Deprecated; This parameter is kept for compatibility
     * @return FailureResponse|FormInteractionResponse|SuccessResponse
     * @since 4.0.0 Maps card token from the seamless response
     * @since 4.0.0 Use notification_url_1 as TermUrl
     */
    public function mapSeamlessResponse($payload, $url = "")
    {
        $this->simpleXml = new SimpleXMLElement('<payment></payment>');

        $this->mapCommonSeamlessFields($payload);
        $this->addCardToken($payload);

        if (array_key_exists(SeamlessFields::ACS_URL, $payload)) {
            $response = $this->makeFormInteractionResponse($payload);
            return $response;
        }

        if ($payload['transaction_state'] == 'success') {
            return new SuccessResponse($this->simpleXml);
        }

        return new FailureResponse($this->simpleXml);
    }

    /**
     * Maps all pre-existing fields the seamless sends.
     *
     * @param $payload
     * @since 4.0.0
     */
    private function mapCommonSeamlessFields($payload)
    {
        $this->simpleXml->addChild(
            ResponseMappingXmlFields::MERCHANT_ACCOUNT_ID,
            $payload[SeamlessFields::MERCHANT_ACCOUNT_ID]
        );

        $this->simpleXml->addChild(
            ResponseMappingXmlFields::TRANSACTION_ID,
            $payload[SeamlessFields::TRANSACTION_ID]
        );

        $this->simpleXml->addChild(
            ResponseMappingXmlFields::TRANSACTION_STATE,
            $payload[SeamlessFields::TRANSACTION_STATE]
        );

        $this->simpleXml->addChild(
            ResponseMappingXmlFields::TRANSACTION_TYPE,
            $payload[SeamlessFields::TRANSACTION_TYPE]
        );

        $this->simpleXml->addChild(
            ResponseMappingXmlFields::PAYMENT_METHOD,
            $payload[SeamlessFields::PAYMENT_METHOD]
        );

        $this->simpleXml->addChild(
            ResponseMappingXmlFields::REQUEST_ID,
            $payload[SeamlessFields::REQUEST_ID]
        );


        $this->addRequestedAmount($payload);
        $this->addThreeDInformation($payload);
        $this->addParentTransactionId($payload);
        $this->addStatuses($payload);
    }

    private function simpleXmlAppendNode(SimpleXMLElement $to, SimpleXMLElement $from)
    {
        $toDom = dom_import_simplexml($to);
        $fromDom = dom_import_simplexml($from);
        $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
    }

    /**
     * Add the requested amount to our XML
     *
     * @param $payload
     * @since 4.0.0
     */
    private function addRequestedAmount($payload)
    {
        if (array_key_exists(SeamlessFields::REQUESTED_AMOUNT, $payload) &&
            array_key_exists(SeamlessFields::REQUESTED_AMOUNT_CURRENCY, $payload)
        ) {
            $amountSimpleXml = new SimpleXMLElement(
                '<requested-amount>'.$payload[SeamlessFields::REQUESTED_AMOUNT].'</requested-amount>'
            );
            $amountSimpleXml->addAttribute(
                ResponseMappingXmlFields::REQUESTED_AMOUNT_CURRENCY,
                $payload[SeamlessFields::REQUESTED_AMOUNT_CURRENCY]
            );
            $this->simpleXmlAppendNode($this->simpleXml, $amountSimpleXml);
        }
    }

    /**
     * Add 3D information to our XML
     *
     * @param $payload
     * @since 4.0.0
     */
    private function addThreeDInformation($payload)
    {
        if (array_key_exists(SeamlessFields::ACS_URL, $payload) &&
            array_key_exists(SeamlessFields::PAREQ, $payload) &&
            array_key_exists(SeamlessFields::CARDHOLDER_AUTHENTICATION_STATUS, $payload)
        ) {
            $threeD = new SimpleXMLElement('<three-d></three-d>');
            $threeD->addChild(ResponseMappingXmlFields::ACS_URL, $payload[SeamlessFields::ACS_URL]);
            $threeD->addChild(ResponseMappingXmlFields::PAREQ, $payload[SeamlessFields::PAREQ]);
            $threeD->addChild(
                ResponseMappingXmlFields::CARDHOLDER_AUTHENTICATION_STATUS,
                $payload[SeamlessFields::CARDHOLDER_AUTHENTICATION_STATUS]
            );
            $this->simpleXmlAppendNode($this->simpleXml, $threeD);
        }
    }

    /**
     * Add the parent transcation id to our XML
     *
     * @param $payload
     * @since 4.0.0
     */
    private function addParentTransactionId($payload)
    {
        if (array_key_exists(SeamlessFields::PARENT_TRANSACTION_ID, $payload)) {
            $this->simpleXml->addChild(
                ResponseMappingXmlFields::PARENT_TRANSACTION_ID,
                $payload[SeamlessFields::PARENT_TRANSACTION_ID]
            );
        }
    }

    /**
     * Add the credit card token to our XML.
     *
     * @param $payload
     * @since 4.0.0
     */
    private function addCardToken($payload)
    {
        if (array_key_exists(SeamlessFields::TOKEN_ID, $payload) &&
            array_key_exists(SeamlessFields::MASKED_ACCOUNT_NUMBER, $payload)
        ) {
            $card_token = new SimpleXMLElement('<card-token></card-token>');
            $card_token->addChild(
                ResponseMappingXmlFields::TOKEN_ID,
                $payload[SeamlessFields::TOKEN_ID]
            );
            $card_token->addChild(
                ResponseMappingXmlFields::MASKED_ACCOUNT_NUMBER,
                $payload[SeamlessFields::MASKED_ACCOUNT_NUMBER]
            );
            $this->simpleXmlAppendNode($this->simpleXml, $card_token);
        }
    }

    /**
     * Add all the status information to our XML.
     *
     * @param $payload
     * @since 4.0.0
     */
    private function addStatuses($payload)
    {
        $statuses = $this->extractStatusesFromResponse($payload);
        if (count($statuses) > 0) {
            $statusesXml = new SimpleXMLElement('<statuses></statuses>');

            foreach ($statuses as $status) {
                $statusXml = $this->makeStatus($status);
                $this->simpleXmlAppendNode($statusesXml, $statusXml);
            }

            $this->simpleXmlAppendNode($this->simpleXml, $statusesXml);
        }
    }

    /**
     * Build a FormInteractionResponse and add the form fields for a successful redirect
     *
     * @param $payload
     * @return FormInteractionResponse
     */
    private function makeFormInteractionResponse($payload)
    {
        if (!array_key_exists(SeamlessFields::PROCESSING_URL, $payload)) {
            throw new MalformedResponseException('Missing notification_url_1 in response');
        }

        $fields = $this->makeFormFields($payload);
        $response = new FormInteractionResponse($this->simpleXml, $payload[SeamlessFields::ACS_URL]);
        $response->setFormFields($fields);

        return $response;
    }

    /**
     * Build the form fields required for a 3DS FormInteractionResponse
     *
     * @param array $payload
     * @return FormFieldMap;
     * @since 4.0.0
     */
    private function makeFormFields($payload)
    {
        $fields = new FormFieldMap();
        $fields->add(self::FORM_FIELD_TERM_URL, (string)$payload[SeamlessFields::PROCESSING_URL]);
        $fields->add(self::FORM_FIELD_PAREQ, (string)$payload[SeamlessFields::PAREQ]);
        $fields->add(
            self::FORM_FIELD_MD,
            http_build_query([
                SeamlessFields::MERCHANT_ACCOUNT_ID => $payload[SeamlessFields::MERCHANT_ACCOUNT_ID],
                SeamlessFields::TRANSACTION_TYPE => $payload[SeamlessFields::TRANSACTION_TYPE],
                SeamlessFields::TRANSACTION_ID => $payload[SeamlessFields::TRANSACTION_ID],
                SeamlessFields::NONCE3D => $payload[SeamlessFields::NONCE3D]
            ])
        );

        return $fields;
    }

    /**
     * Maps status data to a well-formed XML element
     *
     * @param $statusData
     * @return SimpleXMLElement
     * @since 4.0.0
     */
    private function makeStatus($statusData)
    {
        $status = new SimpleXMLElement('<status></status>');
        $status->addAttribute(StatusFields::CODE, $statusData[StatusFields::CODE]);
        $status->addAttribute(StatusFields::DESCRIPTION, $statusData[StatusFields::DESCRIPTION]);
        $status->addAttribute(StatusFields::SEVERITY, $statusData[StatusFields::SEVERITY]);

        return $status;
    }

    /**
     * Turns the statuses from the response into an easier to use array format
     *
     * @param $payload
     * @return array
     * @since 4.0.0
     */
    private function extractStatusesFromResponse($payload)
    {
        $statuses = [];

        foreach ($payload as $key => $value) {
            if (strpos($key, StatusFields::PATTERN) === 0) {
                if (strpos($key, StatusFields::CODE_PATTERN) === 0) {
                    $number = str_replace(StatusFields::CODE_PATTERN, '', $key);
                    $statuses[$number][StatusFields::CODE] = $value;
                }
                if (strpos($key, StatusFields::SEVERITY_PATTERN) === 0) {
                    $number = str_replace(StatusFields::SEVERITY_PATTERN, '', $key);
                    $statuses[$number][StatusFields::SEVERITY] = $value;
                }
                if (strpos($key, StatusFields::DESCRIPTION_PATTERN) === 0) {
                    $number = str_replace(StatusFields::DESCRIPTION_PATTERN, '', $key);
                    $statuses[$number][StatusFields::DESCRIPTION] = $value;
                }
            }
        }

        return $statuses;
    }
}
