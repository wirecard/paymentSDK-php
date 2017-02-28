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

/**
 * Class ThreeDAuthorizationTransaction
 * @package Wirecard\PaymentSdk\Transaction
 *
 * This class is instantiated during the 3D process,
 * and it should not be instantiated by the merchant.

 */
class ThreeDAuthorizationTransaction extends Transaction
{
    const CCARD_AUTHORIZATION = 'authorization';
    const PARAM_PARENT_TRANSACTION_ID = 'parent-transaction-id';

    /**
     * @var string
     */
    private $payload;

    /**
     * ReferenceTransaction constructor.
     * @param array $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @param string|null $operation
     * @return array
     */
    public function mappedProperties($operation = null)
    {
        $md = json_decode(base64_decode($this->payload['MD']), true);
        $parentTransactionId = $md['enrollment-check-transaction-id'];
        $paRes = $this->payload['PaRes'];

        return [
            self::PARAM_TRANSACTION_TYPE => self::CCARD_AUTHORIZATION,
            self::PARAM_PARENT_TRANSACTION_ID => $parentTransactionId,
            'three-d' => [
                'pares' => $paRes
            ],
        ];
    }

    /**
     * @param string|null
     * @return string
     */
    public function getConfigKey($operation = null)
    {
        return ThreeDCreditCardTransaction::class;
    }
}
