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

use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

class SepaTransaction extends Transaction
{
    const DIRECT_DEBIT = 'sepadirectdebit';

    const CREDIT_TRANSFER = 'sepacredit';

    /**
     * @var string
     */
    private $iban;

    /**
     * @var string
     */
    private $bic;

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @param string $operation
     * @param string $parentTransactionType
     * @return array
     */
    protected function mappedSpecificProperties($operation, $parentTransactionType)
    {
        $result = [
            self::PARAM_TRANSACTION_TYPE => $this->retrieveTransactionType($operation, $parentTransactionType)
        ];

        if (null !== $this->iban) {
            $result['bank-account'] = [
                'iban' => $this->iban
            ];
            if (null !== $this->bic) {
                $result['bank-account']['bic'] = $this->bic;
            }
        }

        return $result;
    }

    public function getConfigKey($operation = null, $parentTransactionType = null)
    {
        return $this->retrievePaymentMethodName($operation, $parentTransactionType);
    }

    public function retrievePaymentMethodName($operation = null, $parentTransactionType = null)
    {
        if (Operation::CREDIT === $operation || Operation::CREDIT == $parentTransactionType) {
            return self::CREDIT_TRANSFER;
        }

        return self::DIRECT_DEBIT;
    }

    private function retrieveTransactionType($operation, $parentTransactionType)
    {
        if (Operation::CANCEL === $operation) {
            return 'void-' . $parentTransactionType;
        }

        $txTypeMapping = [
            Operation::RESERVE => 'authorization',
            Operation::PAY => 'pending-debit',
            Operation::CREDIT => 'pending-credit'
        ];

        if (!array_key_exists($operation, $txTypeMapping)) {
            throw new UnsupportedOperationException();
        }

        return $txTypeMapping[$operation];
    }
}
