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
    private $shippingAddressUsage;

    /**
     * @var int
     */
    private $amountTransactionsLastDay;

    /**
     * @var int
     */
    private $amountTransactionsLastYear;


    /**
     * @param $creationDate
     * @return $this
     */
    public function setCreationDate($creationDate = null)
    {
        if (null == $creationDate) {
            //TODO Create date with YYYYMMDDHHMM
            $creationDate = gmdate('YmdHis');
        }
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @param $updateDate
     * @return $this
     */
    public function setUpdateDate($updateDate = null)
    {
        if (null == $updateDate) {
            //TODO Create date with YYYYMMDDHHMM
            $updateDate = gmdate('YmdHis');
        }
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * @param $passChangeDate
     * @return $this
     */
    public function setPassChangeDate($passChangeDate = null)
    {
        if (null == $passChangeDate) {
            //TODO Create date with YYYYMMDDHHMM
            $passChangeDate = gmdate('YmdHis');
        }
        $this->passChangeDate = $passChangeDate;

        return $this;
    }

    /**
     * @param $shippingAddressUsage
     * @return $this
     */
    public function setShippingAddressUsage($shippingAddressUsage = null)
    {
        if (null == $shippingAddressUsage) {
            //TODO Create date with YYYYMMDDHHMM
            $shippingAddressUsage = gmdate('YmdHis');
        }
        $this->shippingAddressUsage = $shippingAddressUsage;

        return $this;
    }

    /**
     * @param $transactionsAmount
     * @return $this
     */
    public function setAmountTransactionsLastDay($transactionsAmount)
    {
        $this->amountTransactionsLastDay = $transactionsAmount;
        return $this;
    }

    /**
     * @param $transactionsAmount
     * @return $this
     */
    public function setAmountTransactionsLastYear($transactionsAmount)
    {
        $this->amountTransactionsLastYear = $transactionsAmount;
        return $this;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $cardHolderAccount = array();
        if (null !== $this->creationDate) {
            $cardHolderAccount['account_creation_date'] = $this->creationDate;
        }

        if (null !== $this->updateDate) {
            $cardHolderAccount['account_update_date'] = $this->updateDate;
        }

        if (null !== $this->passChangeDate) {
            $cardHolderAccount['account_password_change_date'] = $this->passChangeDate;
        }

        if (null !== $this->shippingAddressUsage) {
            $cardHolderAccount['shipping_address_first_usage'] = $this->shippingAddressUsage;
        }

        if (null !== $this->amountTransactionsLastDay) {
            $cardHolderAccount['transactions_last_day'] = $this->amountTransactionsLastDay;
        }

        if (null !== $this->amountTransactionsLastYear) {
            $cardHolderAccount['transactions_last_year'] = $this->amountTransactionsLastYear;
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
}
