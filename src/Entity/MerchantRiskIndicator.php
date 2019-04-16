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
 * Class MerchantRiskIndicator
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.7.0
 */
class MerchantRiskIndicator implements MappableEntity
{
    /**
     * @const string
     */
    const DATE_FORMAT = 'Ymd';

    /**
     * @const array
     */
    const OPTIONAL_FIELDS = [
        'risk_info_delivery_timeframe'   => 'deliveryTimeFrame',
        'risk_info_delivery_mail'        => 'deliveryEmailAddress',
        'risk_info_reorder_items'        => 'reorderItems',
        'risk_info_availability'         => 'availability',
        'risk_info_preorder_date'        => 'preOrderDate',
        'risk_info_gift_amount'          => 'giftAmount',
        'risk_info_gift_amount_currency' => 'giftCurrency',
        'risk_info_gift_card_count'      => 'giftCardCount',
        'iso_transaction_type'           => 'isoTransactionType',
    ];

    /**
     * @var DeliveryTimeFrame
     */
    private $deliveryTimeFrame;

    /**
     * @var string
     */
    private $deliveryEmailAddress;

    /**
     * @var string
     */
    private $reorderItems;

    /**
     * @var string
     */
    private $availability;

    /**
     * @var \DateTime
     */
    private $preOrderDate;

    /**
     * @var int
     */
    private $giftAmount;

    /**
     * @var string
     */
    private $giftCurrency;

    /**
     * @var int
     */
    private $giftCardCount;

    /**
     * @var IsoTransactionType
     */
    private $isoTransactionType;

    /**
     * @param DeliveryTimeFrame $deliveryTimeFrame
     * @return $this
     */
    public function setDeliveryTimeFrame($deliveryTimeFrame)
    {
        $this->deliveryTimeFrame = DeliveryTimeFrame::search($deliveryTimeFrame);
        if (!$this->deliveryTimeFrame) {
            throw new \InvalidArgumentException('Delivery time frame preference is invalid.');
        }

        return $this;
    }

    /**
     * @param string $deliveryEmailAddress
     * @return $this
     */
    public function setDeliveryEmailAddress($deliveryEmailAddress)
    {
        $this->deliveryEmailAddress = $deliveryEmailAddress;

        return $this;
    }

    /**
     * @param string $reorderItems
     * @return $this
     */
    public function setReorderItems($reorderItems)
    {
        if ($reorderItems !== '01' && $reorderItems !== '02') {
            throw new \InvalidArgumentException('Reorder Items preference is invalid.');
        }

        $this->reorderItems = $reorderItems;

        return $this;
    }

    /**
     * @param string $availability
     * @return $this
     */
    public function setAvailability($availability)
    {
        if ($availability !== '01' && $availability !== '02') {
            throw new \InvalidArgumentException('Availability preference is invalid.');
        }

        $this->availability = $availability;

        return $this;
    }

    /**
     * @param \DateTime $preOrderDate
     * @return $this
     */
    public function setPreOrderDate(\DateTime $preOrderDate)
    {
        $this->preOrderDate = $preOrderDate;

        return $this;
    }

    /**
     * @param $giftAmount
     * @return $this
     */
    public function setGiftAmount($giftAmount)
    {
        //@TODO clarify handling (round or cut off) for INT
        $this->giftAmount = $giftAmount;

        return $this;
    }

    /**
     * @param string $giftCurrency
     * @return $this
     */
    public function setGiftCurrency($giftCurrency)
    {
        $this->giftCurrency = $giftCurrency;

        return $this;
    }

    /**
     * @param int $giftCardCount
     * @return $this
     */
    public function setGiftCardCount($giftCardCount)
    {
        if (strlen((string)$giftCardCount) > 2) {
            throw new \InvalidArgumentException('Gift card count must not exceed 2 digits');
        }

        $this->giftCardCount = $giftCardCount;

        return $this;
    }

    public function setIsoTransactionType(IsoTransactionType $isoTransactionType)
    {
        $this->isoTransactionType = DeliveryTimeFrame::search($isoTransactionType);
        if (!$this->isoTransactionType) {
            throw new \InvalidArgumentException('ISO transaction type preference is invalid.');
        }

        return $this;

    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $merchantRiskIndicator = array();

        foreach (self::OPTIONAL_FIELDS as $mappedKey => $property) {
            if (isset($this->{$property})) {
                $cardHolderAccount[$mappedKey] = $this->getFormattedValue($this->{$property});
            }
        }

        return $merchantRiskIndicator;
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
