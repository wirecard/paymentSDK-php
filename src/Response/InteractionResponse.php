<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Response;

use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class InteractionResponse
 * @package Wirecard\PaymentSdk\Response
 *
 * This object is returned,
 * if the payment process was initialized successfully,
 * and an interaction with the consumer browser is required in order to continue it.
 */
class InteractionResponse extends Response
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * InteractionResponse constructor.
     * @param \SimpleXmlElement $simpleXml
     * @param string $redirectUrl - Redirect url of the external service provider
     * @throws MalformedResponseException
     */
    public function __construct($simpleXml, $redirectUrl)
    {
        parent::__construct($simpleXml);
        $this->transactionId = $this->findElement('transaction-id');
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * get the unique transaction id for followup operations
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * get the redirect url used for external service provider redirects
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
