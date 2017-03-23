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

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\Redirect;

/**
 * Class IdealTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class IdealTransaction extends Transaction
{
    const NAME = 'ideal';

    /**
     * @var string
     */
    private $bic;

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * @var string
     */
    private $descriptor;

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @param string $descriptor
     */
    public function setDescriptor($descriptor)
    {
        $this->descriptor = $descriptor;
    }

    /**
     * @param Redirect $redirect
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * @return array
     * @internal param null $requestId
     */
    protected function mappedSpecificProperties()
    {
        $join = (parse_url($this->redirect->getSuccessUrl(), PHP_URL_QUERY) ? '&' : '?');
        $successUrl = $this->redirect->getSuccessUrl() . $join . 'request_id=' . $this->requestId;
        $result = [
            'cancel-redirect-url' => $this->redirect->getCancelUrl(),
            'success-redirect-url' => $successUrl,
        ];
        if (null !== $this->bic) {
            $result['bank-account']['bic'] = $this->bic;
        }
        if (null !== $this->descriptor) {
            $result['descriptor'] = $this->descriptor;
        }
        return $result;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        return $this::TYPE_DEBIT;
    }
}
