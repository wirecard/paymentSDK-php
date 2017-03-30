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

namespace Wirecard\PaymentSdk\Mapper;

use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SimpleXMLElement;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\FormFieldMap;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\PendingResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;
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
     * @var SimpleXMLElement
     */
    protected $simpleXml;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var boolean Whether the response is synchronous or not
     */
    private $syncResponse;

    /**
     * ResponseMapper constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * map the xml Response from engine to ResponseObjects
     *
     * @param string $response
     * @param boolean $validateSignature
     * @param Transaction $transaction
     * @throws \InvalidArgumentException
     * @trhows MalformedResponseException
     * @return Response
     */
    public function map($response, $validateSignature = false, Transaction $transaction = null)
    {
        $this->transaction = $transaction;
        $this->syncResponse = false;

        // If the transaction is provided, the response can only be synchronous.
        // But if the payment method contains provides an url, we want an interaction response.
        if ($transaction !== null) {
            $this->syncResponse = true;
        }

        // If the response is encoded, we need to first decode it.
        $decodedResponse = base64_decode($response);
        $xmlResponse = (base64_encode($decodedResponse) === $response) ? $decodedResponse : $response;
        //we need to use internal_errors, because we don't want to throw errors on invalid xml responses
        $oldErrorHandling = libxml_use_internal_errors(true);
        $this->simpleXml = simplexml_load_string($response);
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

        $validSignature = true;

        if ($validateSignature) {
            $validSignature = $this->validateSignature($xmlResponse);
        }

        switch ($state) {
            case 'success':
                return $this->mapSuccessResponse($validSignature);
            case 'in-progress':
                return new PendingResponse($this->simpleXml, $validSignature);
            default:
                return new FailureResponse($this->simpleXml, $validSignature);
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
     * @return string|null
     */
    private function getSuccessRedirectUrl()
    {
        $paymentMethod = $this->getPaymentMethod();
        if (isset($paymentMethod['url'])) {
            return (string)$paymentMethod['url'];
        }

        if (null !== $this->transaction && null !== $this->transaction->getSuccessUrl()) {
            return $this->transaction->getSuccessUrl();
        }

        return null;
    }

    /**
     * @throws MalformedResponseException
     * @throws \InvalidArgumentException
     * @return FormInteractionResponse
     */
    private function mapThreeDResponse()
    {
        if (!($this->transaction instanceof ThreeDCreditCardTransaction)) {
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

        $fields = new FormFieldMap();
        $fields->add('TermUrl', $this->transaction->getTermUrl());
        if (!isset($threeD->{'pareq'})) {
            throw new MalformedResponseException('Missing pareq in enrollment-check response.');
        }

        $fields->add('PaReq', (string)$threeD->{'pareq'});

        $fields->add(
            'MD',
            base64_encode(json_encode([
                'enrollment-check-transaction-id' => $response->getTransactionId(),
                'operation-type' => $this->transaction->retrieveOperationType()
            ]))
        );

        $response->setFormFields($fields);

        return $response;
    }

    /**
     * @throws MalformedResponseException
     * @return FormInteractionResponse
     */
    private function mapSynchronousResponse()
    {
        $payload = base64_encode($this->simpleXml->asXML());

        $formFields = new FormFieldMap();
        $formFields->add('sync_response', $payload);

        $response = new FormInteractionResponse($this->simpleXml, $this->getSuccessRedirectUrl());
        $response->setFormFields($formFields);

        return $response;
    }

    /**
     * @param boolean $validSignature
     * @throws \InvalidArgumentException
     * @throws MalformedResponseException
     * @return FormInteractionResponse|InteractionResponse|SuccessResponse
     */
    private function mapSuccessResponse($validSignature)
    {
        if ((string)$this->simpleXml->{'transaction-type'} === ThreeDCreditCardTransaction::TYPE_CHECK_ENROLLMENT) {
            return $this->mapThreeDResponse();
        }

        $redirectUrl = $this->getSuccessRedirectUrl();
        if ($redirectUrl === null) {
            return new SuccessResponse($this->simpleXml, $validSignature);
        }

        // For a synchronous result without redirect URL, we also want to return a FormInteractionResponse.
        if ($this->syncResponse === true && !isset($this->getPaymentMethod()['url'])) {
            return $this->mapSynchronousResponse();
        }

        return new InteractionResponse($this->simpleXml, $redirectUrl);
    }
}
