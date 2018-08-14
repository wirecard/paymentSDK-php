<?php

/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Transaction;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;
/**
 * Description of PayolutionTransaction
 *
 * @author Omar Issa
 */
class PayolutionTransaction extends Transaction implements Reservable {

    const NAME = 'payolution';


    protected function mappedSpecificProperties() {
        $transactionType = $this->retrieveTransactionType();
        $data = array();

        if ($this->parentTransactionId) {
            $data['payment-methods'] = ['payment-method' => [['name' => 'payolution-inv']]];
        }
        if ($this->accountHolder instanceof AccountHolder) {
            $data['account-holder'] = $this->accountHolder->mappedProperties();
        } else {
            throw new MandatoryFieldMissingException('Birth Date should be set.');
        }
        if ($this->amount instanceof Amount) {
            $data['requested-amount'] = $this->amount->mappedProperties();
        } else {
            throw new MandatoryFieldMissingException('amount value should not be less than value');
        }

        if (null !== $this->orderNumber) {
            $data['order-number'] = $this->orderNumber;
        }


        if ($this->basket instanceof Basket) {
            $this->basket->setVersion(self::class);
            $data['order-items'] = $this->basket->mappedProperties();
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve() {
        if ($this->parentTransactionId) {
            if ($this->parentTransactionType === self::TYPE_CAPTURE_AUTHORIZATION) {
                return self::TYPE_CAPTURE_AUTHORIZATION;
            }
        }
        return self::TYPE_AUTHORIZATION;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay() {

        if ($this->parentTransactionType === self::TYPE_AUTHORIZATION) {
            return self::TYPE_CAPTURE_AUTHORIZATION;
        }

        return self::TYPE_AUTHORIZATION;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel() {
        if (!$this->parentTransactionId) {
            if ($this->parentTransactionType === self::TYPE_CAPTURE_AUTHORIZATION) {
                return self::TYPE_REFUND_CAPTURE;
            } else {
                throw new UnsupportedOperationException('The transaction can not be refund.');
            }
        }
    }
}
    