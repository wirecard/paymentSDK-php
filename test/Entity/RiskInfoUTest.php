<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\RiskInfoDeliveryTimeFrame;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use Wirecard\PaymentSdk\Entity\RiskInfo;

class RiskInfoUTest extends \PHPUnit_Framework_TestCase
{
    public function testSeamlessMappingWithAllFields()
    {
        $merchantRiskIndicator = new RiskInfo();
        $mail                  = 'max.muster@mail.com';
        $date                  = new \DateTime();

        $expected = [
            'risk_info_delivery_timeframe'   => RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY,
            'risk_info_delivery_mail'        => $mail,
            'risk_info_reorder_items'        => RiskInfoReorder::FIRST_TIME_ORDERED,
            'risk_info_availability'         => RiskInfoAvailability::MERCHANDISE_AVAILABLE,
            'risk_info_preorder_date'        => $date->format(RiskInfo::DATE_FORMAT),
        ];

        $merchantRiskIndicator->setAvailability(RiskInfoAvailability::MERCHANDISE_AVAILABLE);
        $merchantRiskIndicator->setDeliveryEmailAddress($mail);
        $merchantRiskIndicator->setDeliveryTimeFrame(RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY);
        $merchantRiskIndicator->setPreOrderDate($date);
        $merchantRiskIndicator->setReorderItems(RiskInfoReorder::FIRST_TIME_ORDERED);

        $this->assertEquals($expected, $merchantRiskIndicator->mappedSeamlessProperties());
    }

    public function testMappingWithAllFields()
    {
        $merchantRiskIndicator = new RiskInfo();
        $mail                  = 'max.muster@mail.com';
        $date                  = new \DateTime();

        $expected = [
            'delivery-timeframe'   => RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY,
            'delivery-mail'        => $mail,
            'reorder-items'        => RiskInfoReorder::FIRST_TIME_ORDERED,
            'availability'         => RiskInfoAvailability::MERCHANDISE_AVAILABLE,
            'preorder-date'        => $date->format(RiskInfo::DATE_FORMAT),
        ];

        $merchantRiskIndicator->setAvailability(RiskInfoAvailability::MERCHANDISE_AVAILABLE);
        $merchantRiskIndicator->setDeliveryEmailAddress($mail);
        $merchantRiskIndicator->setDeliveryTimeFrame(RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY);
        $merchantRiskIndicator->setPreOrderDate($date);
        $merchantRiskIndicator->setReorderItems(RiskInfoReorder::FIRST_TIME_ORDERED);

        $this->assertEquals($expected, $merchantRiskIndicator->mappedProperties());
    }
}
