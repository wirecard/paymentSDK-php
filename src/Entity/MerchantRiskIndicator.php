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

use Wirecard\PaymentSdk\Constant\RiskInfoDeliveryTimeFrame;
use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\IsoTransactionType;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

/**
 * Class MerchantRiskIndicator
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.7.0
 */
class MerchantRiskIndicator extends Mappable
{
    /**
     * @const string
     */
    const DATE_FORMAT = 'Ymd';

    /**
     * @const array
     */
    const PROPERTY_CONFIGURATION = [
        'deliveryTimeFrame' => [ // String, Enum
            // Example if mapped should be used
            //'mapped' => [
            //  'key' => 'risk-info-delivery-timeframe',
            //  'type' => 'String',
            //],
            'mappedSeamless' => [
                'key'  => 'risk_info_delivery_timeframe',
                'type' => 'String',
            ],
        ],
        'deliveryEmailAddress' => [ // String
            'mappedSeamless' => [
                'key'  => 'risk_info_delivery_mail',
                'type' => 'String', //mail in case we want to verify
            ]
        ],
        'reorderItems' => [ // String, Enum
            'mappedSeamless' => [
                'key'  => 'risk_info_reorder_items',
                'type' => 'String',
            ]
        ],
        'availability' => [ // String, Enum
            'mappedSeamless' => [
                'key'  => 'risk_info_availability',
            ]
        ],
        'preOrderDate' => [ // Date
            'mappedSeamless' => [
                'key'       => 'risk_info_preorder_date',
                'formatter' => 'dateFormatter',
                'formatterParams' => [
                    'dateFormat' => self::DATE_FORMAT
                ],
            ]
        ],
        'giftAmount' => [ // Integer
            'mappedSeamless' => [
                'key'  => 'risk_info_gift_amount',
            ]
        ],
        'giftCurrency' => [ // String
            'mappedSeamless' => [
                'key'  => 'risk_info_gift_amount_currency',
            ]
        ],
        'giftCardCount' => [ // Integer
            'mappedSeamless' => [
                'key'  => 'risk_info_gift_card_count',
            ]
        ],
        'isoTransactionType' => [ // String, Enum
            'mappedSeamless' => [
                'key'  => 'iso_transaction_type',
            ]
        ],
    ];

    /**
     * @var RiskInfoDeliveryTimeFrame
     */
    protected $deliveryTimeFrame;

    /**
     * @var string
     */
    protected $deliveryEmailAddress;

    /**
     * @var RiskInfoReorder
     */
    protected $reorderItems;

    /**
     * @var RiskInfoAvailability
     */
    protected $availability;

    /**
     * @var \DateTime
     */
    protected $preOrderDate;

    /**
     * @var int
     */
    protected $giftAmount;

    /**
     * @var string
     */
    protected $giftCurrency;

    /**
     * @var int
     */
    protected $giftCardCount;

    /**
     * @var IsoTransactionType
     */
    protected $isoTransactionType;

    /**
     * @param $deliveryTimeFrame
     * @return $this
     * @since 3.7.0
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
     * @since 3.7.0
     */
    public function setDeliveryEmailAddress($deliveryEmailAddress)
    {
        $this->deliveryEmailAddress = $deliveryEmailAddress;

        return $this;
    }

    /**
     * @param string $reorderItems
     * @return $this
     * @since 3.7.0
     */
    public function setReorderItems($reorderItems)
    {
        if (!RiskInfoDeliveryTimeFrame::isValid($reorderItems)) {
            throw new \InvalidArgumentException('Reorder Items preference is invalid.');
        }
        $this->reorderItems = $reorderItems;

        return $this;
    }

    /**
     * @param string $availability
     * @return $this
     * @since 3.7.0
     */
    public function setAvailability($availability)
    {
        if (!RiskInfoDeliveryTimeFrame::isValid($availability)) {
            throw new \InvalidArgumentException('Availability preference is invalid.');
        }
        $this->availability = $availability;

        return $this;
    }

    /**
     * @param \DateTime $preOrderDate
     * @return $this
     * @since 3.7.0
     */
    public function setPreOrderDate(\DateTime $preOrderDate)
    {
        $this->preOrderDate = $preOrderDate;

        return $this;
    }

    /**
     * @param Amount $giftAmount
     * @return $this
     * @since 3.7.0
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
     * @since 3.7.0
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
     * @since 3.7.0
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
     * @since 3.7.0
     */
    public function mappedProperties()
    {
        throw new NotImplementedException('mappedProperties() not supported for this entity, 
        mappedSeamlessProperties() only.');
    }
}
