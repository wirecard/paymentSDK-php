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
            self::PROPERTY_MAP_KEY => [
                self::PROPERTY_NAME_KEY => 'delivery-timeframe',
            ]
        ],
        'deliveryEmailAddress' => [ // String
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_delivery_mail',
            ],
            self::PROPERTY_MAP_KEY => [
                self::PROPERTY_NAME_KEY => 'delivery-mail',
            ]
        ],
        'reorderItems' => [ // String, Enum
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_reorder_items',
            ],
            self::PROPERTY_MAP_KEY => [
                self::PROPERTY_NAME_KEY => 'reorder-items',
            ]
        ],
        'availability' => [ // String, Enum
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_availability',
            ],
            self::PROPERTY_MAP_KEY => [
                self::PROPERTY_NAME_KEY => 'availability',
            ]
        ],
        'preOrderDate' => [ // Date
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY       => 'risk_info_preorder_date',
                self::PROPERTY_FORMATTER_KEY => DateFormatter::FORMATTER_NAME,
                self::PROPERTY_FORMATTER_PARAMS_KEY => [
                    DateFormatter::PARAM_DATE_FORMAT_KEY => self::DATE_FORMAT
                ],
            ],
            self::PROPERTY_MAP_KEY => [
                self::PROPERTY_NAME_KEY => 'preorder-date',
                self::PROPERTY_FORMATTER_KEY => DateFormatter::FORMATTER_NAME,
                self::PROPERTY_FORMATTER_PARAMS_KEY => [
                    DateFormatter::PARAM_DATE_FORMAT_KEY => self::DATE_FORMAT
                ]
            ]
        ],
        'giftAmount' => [ // Integer
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_gift_amount',
            ],
            self::PROPERTY_MAP_KEY => [
                self::PROPERTY_NAME_KEY => 'gift-amount',
            ]
        ],
        'giftCurrency' => [ // String
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_gift_amount_currency',
            ],
            self::PROPERTY_MAP_KEY => [
                self::PROPERTY_NAME_KEY => 'gift-amount-currency',
            ]
        ],
        'giftCardCount' => [ // Integer
            self::PROPERTY_MAP_SEAMLESS_KEY => [
                self::PROPERTY_NAME_KEY  => 'risk_info_gift_card_count',
            ],
            self::PROPERTY_MAP_KEY => [
                self::PROPERTY_NAME_KEY => 'gift-card-count',
            ]
        ]
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
     * @return RiskInfoDeliveryTimeFrame
     * @since 3.8.0
     */
    public function getDeliveryTimeFrame()
    {
        return $this->deliveryTimeFrame;
    }

    /**
     * @return string
     * @since 3.8.0
     */
    public function getDeliveryEmailAddress()
    {
        return $this->deliveryEmailAddress;
    }

    /**
     * @return RiskInfoReorder
     * @since 3.8.0
     */
    public function getReorderItems()
    {
        return $this->reorderItems;
    }

    /**
     * @return RiskInfoAvailability
     * @since 3.8.0
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @return \DateTime
     * @since 3.8.0
     */
    public function getPreOrderDate()
    {
        return $this->preOrderDate;
    }

    /**
     * @return int
     * @since 3.8.0
     */
    public function getGiftAmount()
    {
        return $this->giftAmount;
    }

    /**
     * @return string
     * @since 3.8.0
     */
    public function getGiftCurrency()
    {
        return $this->giftCurrency;
    }

    /**
     * @return int
     * @since 3.8.0
     */
    public function getGiftCardCount()
    {
        return $this->giftCardCount;
    }
}
