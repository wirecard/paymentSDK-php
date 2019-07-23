<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Exception\NotImplementedException;

/**
 * Class CardHolderAccount
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.8.0
 */
class CardHolderAccount implements MappableEntity
{
    /** @const string DATE_FORMAT */
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    /** @const array OPTIONAL_FIELDS */
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
     * @since 3.8.0
     */
    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @param $updateDate
     * @return $this
     * @since 3.8.0
     */
    public function setUpdateDate(\DateTime $updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * @param $passChangeDate
     * @return $this
     * @since 3.8.0
     */
    public function setPassChangeDate(\DateTime $passChangeDate)
    {
        $this->passChangeDate = $passChangeDate;

        return $this;
    }

    /**
     * @param $shippingAddressFirstUse
     * @return $this
     * @since 3.8.0
     */
    public function setShippingAddressFirstUse(\DateTime $shippingAddressFirstUse)
    {
        $this->shippingAddressFirstUse = $shippingAddressFirstUse;

        return $this;
    }

    /**
     * @param \DateTime $cardCreationDate
     * @return $this
     * @since 3.8.0
     */
    public function setCardCreationDate(\DateTime $cardCreationDate)
    {
        $this->cardCreationDate = $cardCreationDate;

        return $this;
    }

    /**
     * @param $transactionsAmount
     * @return $this
     * @since 3.8.0
     */
    public function setAmountTransactionsLastDay($transactionsAmount)
    {
        $this->amountTransactionsLastDay = (int)$transactionsAmount;

        return $this;
    }

    /**
     * @param $transactionsAmount
     * @return $this
     * @since 3.8.0
     */
    public function setAmountTransactionsLastYear($transactionsAmount)
    {
        $this->amountTransactionsLastYear = (int)$transactionsAmount;

        return $this;
    }

    /**
     * @param $transactionsAmount
     * @return $this
     * @since 3.8.0
     */
    public function setAmountCardTransactionsLastDay($transactionsAmount)
    {
        $this->amountCardTransactionsLastDay = (int)$transactionsAmount;

        return $this;
    }

    /**
     * @param $purchasesAmount
     * @return $this
     * @since 3.8.0
     */
    public function setAmountPurchasesLastSixMonths($purchasesAmount)
    {
        $this->amountPurchasesLastSixMonths = (int)$purchasesAmount;

        return $this;
    }

    /**
     * @param bool $suspiciousActivity
     * @return $this
     * @since 3.8.0
     */
    public function setSuspiciousActivity($suspiciousActivity)
    {
        if ($suspiciousActivity) {
            $this->suspiciousActivity = '02';
            return $this;
        }

        $this->suspiciousActivity = '01';

        return $this;
    }

    /**
     * @param string $merchantCrmId
     * @return $this
     * @since 3.8.0
     */
    public function setMerchantCrmId($merchantCrmId)
    {
        if (mb_strlen((string)$merchantCrmId) > 64) {
            throw new \InvalidArgumentException('Max length for the crm id is 64.');
        }
        $this->merchantCrmId = $merchantCrmId;

        return $this;
    }


    /**
     * @return array|void
     * @throws NotImplementedException
     * @since 3.8.0
     */
    public function mappedProperties()
    {
        throw new NotImplementedException('mappedProperties() not supported for this entity, 
        mappedSeamlessProperties() only.');
    }

    /**
     * @return array
     * @since 3.8.0
     */
    public function mappedSeamlessProperties()
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
     * @param $value
     * @return mixed
     * @since 3.8.0
     */
    private function getFormattedValue($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format(self::DATE_FORMAT);
        }

        return $value;
    }
}
