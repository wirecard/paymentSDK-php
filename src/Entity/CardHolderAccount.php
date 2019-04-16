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

namespace Wirecard\PaymentSdk\Entity;

/**
 * Class CardHolderAccount
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.7.0
 */
class CardHolderAccount implements MappableEntity
{
    /**
     * @const string
     */
    const DATE_FORMAT = 'Ymd';

    /**
     * @const array
     */
    const OPTIONAL_FIELDS = [
        'account_creation_date'        => 'creationDate',
        'account_update_date'          => 'updateDate',
        'account_password_change_date' => 'passChangeDate',
        'shipping_address_first_use'   => 'shippingAddressFirstUse',
        'card_creation_date'           => 'cardCreationDate',
        'transactions_last_day'        => 'amountTransactionsLastDay',
        'transactions_last_year'       => 'amountTransactionsLastYear',
        'card_transactions_last_day'   => 'amountCardTransactionsLastDay',
        'purchases_last_six_months'    => 'amountPurchasesLastSixMonths',
        'suspicious_activity'          => 'suspiciousActivity',
        'merchant_crm_id'              => 'merchantCrmId',
    ];

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var \DateTime
     */
    private $updateDate;

    /**
     * @var \DateTime
     */
    private $passChangeDate;

    /**
     * @var \DateTime
     */
    private $shippingAddressFirstUse;

    /**
     * @var \DateTime
     */
    private $cardCreationDate;

    /**
     * @var int
     */
    private $amountTransactionsLastDay;

    /**
     * @var int
     */
    private $amountTransactionsLastYear;

    /**
     * @var int
     */
    private $amountCardTransactionsLastDay;

    /**
     * @var int
     */
    private $amountPurchasesLastSixMonths;

    /**
     * @var bool
     */
    private $suspiciousActivity;

    /**
     * @var string
     */
    private $merchantCrmId;

    /**
     * @param $creationDate
     * @return $this
     */
    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @param $updateDate
     * @return $this
     */
    public function setUpdateDate(\DateTime $updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * @param $passChangeDate
     * @return $this
     */
    public function setPassChangeDate(\DateTime $passChangeDate)
    {
        $this->passChangeDate = $passChangeDate;

        return $this;
    }

    /**
     * @param $shippingAddressFirstUse
     * @return $this
     */
    public function setShippingAddressFirstUse(\DateTime $shippingAddressFirstUse)
    {
        $this->shippingAddressFirstUse = $shippingAddressFirstUse;

        return $this;
    }

    /**
     * @param \DateTime $cardCreationDate
     * @return $this
     */
    public function setCardCreationDate(\DateTime $cardCreationDate)
    {
        $this->cardCreationDate = $cardCreationDate;

        return $this;
    }

    /**
     * @param $transactionsAmount
     * @return $this
     */
    public function setAmountTransactionsLastDay($transactionsAmount)
    {
        $this->amountTransactionsLastDay = (int)$transactionsAmount;

        return $this;
    }

    /**
     * @param $transactionsAmount
     * @return $this
     */
    public function setAmountTransactionsLastYear($transactionsAmount)
    {
        $this->amountTransactionsLastYear = (int)$transactionsAmount;

        return $this;
    }

    /**
     * @param $transactionsAmount
     * @return $this
     */
    public function setAmountCardTransactionsLastDay($transactionsAmount)
    {
        $this->amountCardTransactionsLastDay = (int)$transactionsAmount;

        return $this;
    }

    /**
     * @param $purchasesAmount
     * @return $this
     */
    public function setAmountPurchasesLastSixMonths($purchasesAmount)
    {
        $this->amountPurchasesLastSixMonths = (int)$purchasesAmount;

        return $this;
    }

    /**
     * @param $suspiciousActivity
     * @return $this
     */
    public function setSuspiciousActivity($suspiciousActivity)
    {
        $this->suspiciousActivity = $suspiciousActivity;

        return $this;
    }

    /**
     * @param $merchantCrmId
     * @return $this
     */
    public function setMerchantCrmId($merchantCrmId)
    {
        $this->merchantCrmId = $merchantCrmId;

        return $this;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $cardHolderAccount = array();

        foreach (self::OPTIONAL_FIELDS as $mappedKey => $property) {
            if (isset($this->{$property})) {
                $cardHolderAccount[$mappedKey] = $this->getFormattedValue($this->{$property});
            }
        }

        return $cardHolderAccount;
    }

    /**
     * @return array
     */
    public function mappedSeamlessProperties()
    {
        return $this->mappedProperties();
    }

    /**
     * @param $value
     * @return mixed
     */
    private function getFormattedValue($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format(self::DATE_FORMAT);
        }

        return $value;
    }
}
