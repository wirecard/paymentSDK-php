<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Constant\RiskInfoDeliveryTimeFrame;
use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\IsoTransactionType;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use Wirecard\PaymentSdk\Exception\NotImplementedException;
use Wirecard\PaymentSdk\Formatter\DateFormatter;

/**
 * Class RiskInfo
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.8.0
 */
class RiskInfo extends Mappable
{
    /** @const string DATE_FORMAT */
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    /** @const array PROPERTY_CONFIGURATION */
    const PROPERTY_CONFIGURATION = [
        'deliveryTimeFrame' => [ // String, Enum
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_delivery_timeframe',
            ],
        ],
        'deliveryEmailAddress' => [ // String
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_delivery_mail',
            ]
        ],
        'reorderItems' => [ // String, Enum
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_reorder_items',
            ]
        ],
        'availability' => [ // String, Enum
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_availability',
            ]
        ],
        'preOrderDate' => [ // Date
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY       => 'risk_info_preorder_date',
                self::PROPERTY_FORMATTER_KEY => DateFormatter::FORMATTER_NAME,
                self::PROPERTY_FORMATTER_PARAMS_KEY => [
                    DateFormatter::PARAM_DATE_FORMAT_KEY => self::DATE_FORMAT
                ],
            ]
        ],
        'giftAmount' => [ // Integer
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_gift_amount',
            ]
        ],
        'giftCurrency' => [ // String
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_gift_amount_currency',
            ]
        ],
        'giftCardCount' => [ // Integer
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_gift_card_count',
            ]
        ],
        'isoTransactionType' => [ // String, Enum
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'iso_transaction_type',
            ]
        ],
    ];

    /**
     * @var RiskInfoDeliveryTimeFrame $deliveryTimeFrame
     */
    protected $deliveryTimeFrame;

    /**
     * @var string $deliveryEmailAddress
     */
    protected $deliveryEmailAddress;

    /** @var RiskInfoReorder $reorderItems */
    protected $reorderItems;

    /** @var RiskInfoAvailability $availability */
    protected $availability;

    /** @var \DateTime $preOrderDate */
    protected $preOrderDate;

    /** @var int $giftAmount */
    protected $giftAmount;

    /** @var string $giftCurrency */
    protected $giftCurrency;

    /** @var int $giftCardCount */
    protected $giftCardCount;

    /** @var IsoTransactionType $isoTransactionType */
    protected $isoTransactionType;

    /**
     * @param $deliveryTimeFrame
     * @return $this
     * @since 3.8.0
     */
    public function setDeliveryTimeFrame($deliveryTimeFrame)
    {
        if (!RiskInfoDeliveryTimeFrame::isValid($deliveryTimeFrame)) {
            throw new \InvalidArgumentException('Delivery time frame preference is invalid.');
        }
        $this->deliveryTimeFrame = $deliveryTimeFrame;

        return $this;
    }

    /**
     * @param string $deliveryEmailAddress
     * @return $this
     * @since 3.8.0
     */
    public function setDeliveryEmailAddress($deliveryEmailAddress)
    {
        $this->deliveryEmailAddress = $deliveryEmailAddress;

        return $this;
    }

    /**
     * @param string $reorderItems
     * @return $this
     * @since 3.8.0
     */
    public function setReorderItems($reorderItems)
    {
        if (!RiskInfoReorder::isValid($reorderItems)) {
            throw new \InvalidArgumentException('Reorder Items preference is invalid.');
        }
        $this->reorderItems = $reorderItems;

        return $this;
    }

    /**
     * @param string $availability
     * @return $this
     * @since 3.8.0
     */
    public function setAvailability($availability)
    {
        if (!RiskInfoAvailability::isValid($availability)) {
            throw new \InvalidArgumentException('Availability preference is invalid.');
        }
        $this->availability = $availability;

        return $this;
    }

    /**
     * @param \DateTime $preOrderDate
     * @return $this
     * @since 3.8.0
     */
    public function setPreOrderDate(\DateTime $preOrderDate)
    {
        $this->preOrderDate = $preOrderDate;

        return $this;
    }

    /**
     * @param Amount $giftAmount
     * @return $this
     * @since 3.8.0
     */
    public function setGiftAmount(Amount $giftAmount)
    {
        $this->giftAmount   = (int)floor($giftAmount->getValue());
        $this->giftCurrency = $giftAmount->getCurrency();

        return $this;
    }

    /**
     * @param int $giftCardCount
     * @return $this
     * @since 3.8.0
     */
    public function setGiftCardCount($giftCardCount)
    {
        if ($giftCardCount < 1 || $giftCardCount > 99) {
            throw new \InvalidArgumentException('Gift card count must not exceed 2 digits');
        }

        $this->giftCardCount = $giftCardCount;

        return $this;
    }

    /**
     * @param $isoTransactionType
     * @return $this
     * @since 3.8.0
     */
    public function setIsoTransactionType($isoTransactionType)
    {
        if (!IsoTransactionType::isValid($isoTransactionType)) {
            throw new \InvalidArgumentException('ISO transaction type preference is invalid.');
        }

        $this->isoTransactionType = $isoTransactionType;

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
}
