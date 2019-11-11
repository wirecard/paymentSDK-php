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
use Wirecard\PaymentSdk\Constant\FormFields;
use Wirecard\PaymentSdk\Constant\SeamlessFields;
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
        $fields->add(FormFields::FORM_FIELD_TERM_URL, $this->transaction->getTermUrl());
        $fields->add(FormFields::FORM_FIELD_PAREQ, (string)$threeD->{'pareq'});
        //field md is constructed with seamless fields as the keys are the same
        $fields->add(
            FormFields::FORM_FIELD_MD,
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
        $formFields->add(FormFields::FORM_FIELD_SYNC_RESPONSE, $payload);

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
}
