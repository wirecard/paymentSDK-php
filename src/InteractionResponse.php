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

/**
 * Class InteractionResponse
 *
 * This object is returned,
 * if the payment process was initialized successfully,
 * and an interaction with the consumer browser is required in order to continue it.
 * @package Wirecard\PaymentSdk
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
     * @param string $rawData - JSON string holding the raw response data
     * @param StatusCollection $statusCollection
     * @param string $transactionId - unique transaction id for followups
     * @param string $redirectUrl - Redirect url of the external service provider
     */
    public function __construct($rawData, $statusCollection, $transactionId, $redirectUrl)
    {
        parent::__construct($rawData, $statusCollection);
        $this->transactionId = $transactionId;
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
