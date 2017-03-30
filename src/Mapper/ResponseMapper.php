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
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
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
     * @param string $xmlResponse
     * @param boolean $validateSignature
     * @param string $operation
     * @param ThreeDCreditCardTransaction $transaction
     * @return Response
     * @throws MalformedResponseException
     */
    public function map(
        $xmlResponse,
        $validateSignature = false,
        $operation = null,
        ThreeDCreditCardTransaction $transaction = null
    ) {
        $decodedResponse = base64_decode($xmlResponse);
        $xmlResponse = (base64_encode($decodedResponse) === $xmlResponse) ? $decodedResponse : $xmlResponse;
        //we need to use internal_errors, because we don't want to throw errors on invalid xml responses
        $oldErrorHandling = libxml_use_internal_errors(true);
        $this->simpleXml = simplexml_load_string($xmlResponse);
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
                return $this->mapSuccessResponse($operation, $validSignature, $transaction);
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
     * @return mixed
     * @throws MalformedResponseException
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
            throw new MalformedResponseException('Missing payment methods in response');
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
     * @param \SimpleXMLElement $paymentMethod
     * @return string|null
     */
    private function getRedirectUrl(\SimpleXMLElement $paymentMethod)
    {
        if (isset($paymentMethod['url'])) {
            return (string)$paymentMethod['url'];
        }

        return null;
    }

    /**
     * @param $payload
     * @param $operation
     * @param ThreeDCreditCardTransaction $transaction
     * @throws MalformedResponseException
     * @return FormInteractionResponse
     */
    private function mapThreeDResponse(
        $payload,
        $operation,
        ThreeDCreditCardTransaction $transaction
    ) {
        if (!isset($this->simpleXml->{'three-d'})) {
            throw new MalformedResponseException('Missing three-d element in enrollment-check response.');
        }

        $threeD = $this->simpleXml->{'three-d'};

        if (!isset($threeD->{'acs-url'})) {
            throw new MalformedResponseException('Missing acs redirect url in enrollment-check response.');
        }

        $redirectUrl = (string)$threeD->{'acs-url'};

        $response = new FormInteractionResponse($payload, $redirectUrl);

        $fields = new FormFieldMap();
        $fields->add('TermUrl', $transaction->getTermUrl());
        if (!isset($threeD->{'pareq'})) {
            throw new MalformedResponseException('Missing pareq in enrollment-check response.');
        }

        $fields->add('PaReq', (string)$threeD->{'pareq'});

        $fields->add(
            'MD',
            base64_encode(json_encode([
                'enrollment-check-transaction-id' => $response->getTransactionId(),
                'operation-type' => ($operation === Operation::RESERVE)
                    ? Transaction::TYPE_AUTHORIZATION : CreditCardTransaction::TYPE_PURCHASE
            ]))
        );

        $response->setFormFields($fields);

        return $response;
    }

    /**
     * @param string $operation
     * @param boolean $validSignature
     * @param ThreeDCreditCardTransaction $transaction
     * @return FormInteractionResponse|InteractionResponse|SuccessResponse
     * @throws MalformedResponseException
     */
    private function mapSuccessResponse(
        $operation,
        $validSignature,
        ThreeDCreditCardTransaction $transaction = null
    ) {
        if ((string)$this->simpleXml->{'transaction-type'} === ThreeDCreditCardTransaction::TYPE_CHECK_ENROLLMENT) {
            return $this->mapThreeDResponse($this->simpleXml, $operation, $transaction);
        }

        $paymentMethod = $this->getPaymentMethod();
        $redirectUrl = $this->getRedirectUrl($paymentMethod);
        if ($redirectUrl !== null) {
            return new InteractionResponse($this->simpleXml, $redirectUrl);
        }

        return new SuccessResponse($this->simpleXml, $validSignature);
    }
}
