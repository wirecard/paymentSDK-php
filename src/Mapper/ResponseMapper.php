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

use Wirecard\PaymentSdk\Entity\FormFieldMap;
use Wirecard\PaymentSdk\Entity\PaymentMethod\CreditCard;
use Wirecard\PaymentSdk\Entity\PaymentMethod\ThreeDCreditCard;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Class ResponseMapper
 * @package Wirecard\PaymentSdk\Mapper
 */
class ResponseMapper
{
    /**
     * map the xml Response from engine to ResponseObjects
     *
     * @param $xmlResponse
     * @param CreditCard $transaction
     * @return Response
     * @throws MalformedResponseException
     */
    public function map($xmlResponse, CreditCard $transaction = null)
    {
        $decodedResponse = base64_decode($xmlResponse);
        $xmlResponse = (base64_encode($decodedResponse) === $xmlResponse) ? $decodedResponse : $xmlResponse;
        //we need to use internal_errors, because we don't want to throw errors on invalid xml responses
        $oldErrorHandling = libxml_use_internal_errors(true);
        $response = simplexml_load_string($xmlResponse);
        //reset to old value after string is loaded
        libxml_use_internal_errors($oldErrorHandling);
        if (!$response instanceof \SimpleXMLElement) {
            throw new MalformedResponseException('Response is not a valid xml string.');
        }

        //we have to string cast all fields, otherwise the contain SimpleXMLElements

        if (isset($response->{'transaction-state'})) {
            $state = (string)$response->{'transaction-state'};
        } else {
            throw new MalformedResponseException('Missing transaction state in response.');
        }

        $statusCollection = $this->getStatusCollection($response);
        if ($state !== 'success') {
            return new FailureResponse($xmlResponse, $statusCollection);
        } else {
            return $this->mapSuccessResponse($xmlResponse, $response, $statusCollection, $transaction);
        }
    }

    /**
     * get the collection of status returned by elastic engine
     * @param \SimpleXMLElement $payment
     * @return StatusCollection
     * @throws MalformedResponseException
     */
    private function getStatusCollection($payment)
    {
        $collection = new StatusCollection();

        /**
         * @var $statuses \SimpleXMLElement
         */
        $statuses = $payment->statuses;
        if (count($statuses->status) > 0) {
            foreach ($statuses->status as $statusNode) {
                /**
                 * @var $statusNode \SimpleXMLElement
                 */
                $attributes = $statusNode->attributes();

                if ((string)$attributes['code'] !== '') {
                    $code = (string)$attributes['code'];
                } else {
                    throw new MalformedResponseException('Missing status code in response.');
                }
                if ((string)$attributes['description'] !== '') {
                    $description = (string)$attributes['description'];
                } else {
                    throw new MalformedResponseException('Missing status description in response.');
                }
                if ((string)$attributes['severity'] !== '') {
                    $severity = (string)$attributes['severity'];
                } else {
                    throw new MalformedResponseException('Missing status severity in response.');
                }
                $status = new Status($code, $description, $severity);
                $collection->add($status);
            }
        } else {
            throw new MalformedResponseException('Statuses is empty in response.');
        }

        return $collection;
    }

    /**
     * @param \SimpleXMLElement $response
     * @return string
     * @throws MalformedResponseException
     */
    private function getTransactionId(\SimpleXMLElement $response)
    {
        if (isset($response->{'transaction-id'})) {
            return (string)$response->{'transaction-id'};
        } else {
            throw new MalformedResponseException('Missing transaction-id in response');
        }
    }

    /**
     * @param \SimpleXMLElement $response
     * @return mixed
     * @throws MalformedResponseException
     */
    private function getPaymentMethod(\SimpleXMLElement $response)
    {
        if (isset($response->{'payment-methods'})) {
            $paymentMethods = $response->{'payment-methods'};
        } elseif (isset($response->{'card-token'})) {
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
        } else {
            throw new MalformedResponseException('More payment methods in response.');
        }
    }

    /**
     * @param \SimpleXMLElement $paymentMethod
     * @return string|null
     */
    private function getRedirectUrl(\SimpleXMLElement $paymentMethod)
    {
        if (isset($paymentMethod['url'])) {
            return (string)$paymentMethod['url'];
        } else {
            return null;
        }
    }

    /**
     * @param $xmlResponse
     * @return string
     * @throws MalformedResponseException
     */
    private function getProviderTransactionId($xmlResponse)
    {
        $result = null;
        foreach ($xmlResponse->{'statuses'}->{'status'} as $status) {
            if ($result === null) {
                $result = $status['provider-transaction-id'];
            }

            if (strcmp($result, $status['provider-transaction-id']) !== 0) {
                throw new MalformedResponseException('More different provider transaction ID-s in response.');
            }
        }

        return (string)$result;
    }

    private function mapThreeDResponse($payload, $response, $status, ThreeDCreditCard $transaction)
    {
        if (!isset($response->{'three-d'})) {
            throw new MalformedResponseException('Missing three-d element in enrollment-check response.');
        } else {
            $threeD = $response->{'three-d'};
        }
        if (!isset($threeD->{'acs-url'})) {
            throw new MalformedResponseException('Missing acs redirect url in enrollment-check response.');
        } else {
            $redirectUrl = (string)$threeD->{'acs-url'};
        }
        $fields = new FormFieldMap();
        $fields->add('TermUrl', $transaction->getTermUrl());
        if (!isset($threeD->{'pareq'})) {
            throw new MalformedResponseException('Missing pareq in enrollment-check response.');
        } else {
            $fields->add('PaReq', (string)$threeD->{'pareq'});
        }

        $fields->add(
            'MD',
            base64_encode(json_encode(['enrollment-check-transaction-id' => (string)$this->getTransactionId($response),
            'operation-type' => RequestMapper::CCARD_AUTHORIZATION]))
        );

        return new FormInteractionResponse($payload, $status, $redirectUrl, $fields);
    }

    /**
     * @param $xmlResponse
     * @param $response
     * @param $statusCollection
     * @param CreditCard $transaction
     * @return FormInteractionResponse|InteractionResponse|SuccessResponse
     * @throws MalformedResponseException
     */
    private function mapSuccessResponse($xmlResponse, $response, $statusCollection, CreditCard $transaction = null)
    {
        if ($transaction instanceof ThreeDCreditCard) {
            return $this->mapThreeDResponse($xmlResponse, $response, $statusCollection, $transaction);
        }

        $transactionId = $this->getTransactionId($response);

        $paymentMethod = $this->getPaymentMethod($response);
        $redirectUrl = $this->getRedirectUrl($paymentMethod);
        if ($redirectUrl !== null) {
            return new InteractionResponse($xmlResponse, $statusCollection, $transactionId, $redirectUrl);
        } else {
            $providerTransactionId = $this->getProviderTransactionId($response);
            return new SuccessResponse(
                $xmlResponse,
                $statusCollection,
                $transactionId,
                $providerTransactionId
            );
        }
    }
}
