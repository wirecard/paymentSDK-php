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
    /**
     * @const string DATE_FORMAT
     */
    const DATE_FORMAT = 'Y-m-d';

    /** @const array NVP_FIELDS */
    const NVP_FIELDS = [
        'risk_info_delivery_timeframe'  => 'deliveryTimeFrame',
        'risk_info_delivery_mail'       => 'deliveryEmailAddress',
        'risk_info_reorder_items'       => 'reorderItems',
        'risk_info_availability'        => 'availability',
        'risk_info_preorder_date'       => 'preOrderDate',
    ];

    /** @const array REST_FIELDS */
    const REST_FIELDS = [
        'delivery-timeframe'            => 'deliveryTimeFrame',
        'delivery-mail'                 => 'deliveryEmailAddress',
        'reorder-items'                 => 'reorderItems',
        'availability'                  => 'availability',
        'preorder-date'                 => 'preOrderDate',
    ];

    /**
     * @var RiskInfoDeliveryTimeFrame $deliveryTimeFrame
     */
    protected $deliveryTimeFrame;

    /**
     * @var string $deliveryEmailAddress
     */
    protected $deliveryEmailAddress;

    /**
     * @var RiskInfoReorder $reorderItems
     */
    protected $reorderItems;

    /**
     * @var RiskInfoAvailability $availability
     */
    protected $availability;

    /**
     * @var \DateTime $preOrderDate
     */
    protected $preOrderDate;

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
     * @param array $mapping
     * @return array
     * @since 3.8.0
     */
    public function mapProperties($mapping)
    {
        $riskInfo = array();

        foreach ($mapping as $mappedKey => $property) {
            if (isset($this->{$property})) {
                $riskInfo[$mappedKey] = $this->getFormattedValue($this->{$property});
            }
        }

        return $riskInfo;
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
