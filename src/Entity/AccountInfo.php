<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Constant\AuthMethod;

/**
 * Class AccountInfo
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.8.0
 */
class AccountInfo implements MappableEntity
{
    /**
     * @const string DATE_FORMAT
     */
    const DATE_FORMAT = 'Y-m-d';

    /** @const array NVP_FIELDS */
    const NVP_FIELDS = [
        'authentication_method'        => 'authMethod',
        'authentication_timestamp'     => 'authTimestamp',
        'challenge_indicator'          => 'challengeInd',
        'account_creation_date'        => 'creationDate',
        'account_update_date'          => 'updateDate',
        'account_password_change_date' => 'passChangeDate',
        'shipping_address_first_use'   => 'shippingAddressFirstUse',
        'card_creation_date'           => 'cardCreationDate',
        'transactions_last_day'        => 'amountTransactionsLastDay',
        'transactions_last_year'       => 'amountTransactionsLastYear',
        'card_transactions_last_day'   => 'amountCardTransactionsLastDay',
        'purchases_last_six_months'    => 'amountPurchasesLastSixMonths',
    ];

    /** @const array REST_FIELDS */
    const REST_FIELDS = [
        'authentication-method'        => 'authMethod',
        'authentication-timestamp'     => 'authTimestamp',
        'challenge-indicator'          => 'challengeInd',
        'creation-date'                => 'creationDate',
        'update-date'                  => 'updateDate',
        'password-change-date'         => 'passChangeDate',
        'shipping-address-first-use'   => 'shippingAddressFirstUse',
        'card-creation-date'           => 'cardCreationDate',
        'transactions-last-day'        => 'amountTransactionsLastDay',
        'transactions-last-year'       => 'amountTransactionsLastYear',
        'card-transactions-last-day'   => 'amountCardTransactionsLastDay',
        'purchases-last-six-months'    => 'amountPurchasesLastSixMonths',
    ];

    /**
     * @var AuthMethod
     */
    private $authMethod;

    /**
     * @var \DateTime
     */
    private $authTimestamp;

    /**
     * @var ChallengeInd
     */
    private $challengeInd;

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
     * @param $authMethod
     * @return $this
     * @since 3.8.0
     */
    public function setAuthMethod($authMethod)
    {
        if (!AuthMethod::isValid($authMethod)) {
            throw new MandatoryFieldMissingException('Authentication method is not supported.');
        }

        $this->authMethod = $authMethod;

        return $this;
    }

    /**
     * @param $authTimestamp
     * @return $this
     * @since 3.8.0
     */
    public function setAuthTimestamp($authTimestamp = null)
    {
        if (null == $authTimestamp) {
            $authTimestamp = gmdate('Y-m-d\TH:i:s\Z');
        }

        $this->authTimestamp = $authTimestamp;

        return $this;
    }

    /**
     * @param string $challengeInd
     * @return $this
     * @since 3.8.0
     */
    public function setChallengeInd($challengeInd)
    {
        if (!ChallengeInd::isValid($challengeInd)) {
            throw new \InvalidArgumentException('Challenge indication preference is invalid.');
        }

        $this->challengeInd = $challengeInd;

        return $this;
    }

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
     * @return \DateTime
     * @since 3.8.0
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @return \DateTime
     * @since 3.8.0
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @return \DateTime
     * @since 3.8.0
     */
    public function getPassChangeDate()
    {
        return $this->passChangeDate;
    }

    /**
     * @return \DateTime
     * @since 3.8.0
     */
    public function getShippingAddressFirstUse()
    {
        return $this->shippingAddressFirstUse;
    }

    /**
     * @return \DateTime
     * @since 3.8.0
     */
    public function getCardCreationDate()
    {
        return $this->cardCreationDate;
    }

    /**
     * @return int
     * @since 3.8.0
     */
    public function getAmountTransactionsLastDay()
    {
        return $this->amountTransactionsLastDay;
    }

    /**
     * @return int
     * @since 3.8.0
     */
    public function getAmountTransactionsLastYear()
    {
        return $this->amountTransactionsLastYear;
    }

    /**
     * @return int
     * @since 3.8.0
     */
    public function getAmountCardTransactionsLastDay()
    {
        return $this->amountCardTransactionsLastDay;
    }

    /**
     * @return int
     * @since 3.8.0
     */
    public function getAmountPurchasesLastSixMonths()
    {
        return $this->amountPurchasesLastSixMonths;
    }

    /**
     * @param array $mapping
     * @return array
     * @since 3.8.0
     */
    public function mapProperties($mapping)
    {
        $accountInfo = array();

        foreach ($mapping as $mappedKey => $property) {
            if (isset($this->{$property})) {
                if ($mappedKey === 'authTimestamp') {
                    $accountInfo[$mappedKey] = $this->{$property}->format('Y-m-d\TH:i:s');
                    continue;
                }
                $accountInfo[$mappedKey] = $this->getFormattedValue($this->{$property});
            }
        }

        return $accountInfo;
    }

    /**
     * @return array
     * @since 3.8.0
     */
    public function mappedProperties()
    {
        return $this->mapProperties(self::REST_FIELDS);
    }

    /**
     * @return array
     * @since 3.8.0
     */
    public function mappedSeamlessProperties()
    {
        return $this->mapProperties(self::NVP_FIELDS);
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
