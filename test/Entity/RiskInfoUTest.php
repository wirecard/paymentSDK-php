<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Constant\IsoTransactionType;
use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\RiskInfoDeliveryTimeFrame;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\RiskInfo;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

class RiskInfoUTest extends \PHPUnit_Framework_TestCase
{
    public function testSeamlessMappingWithGiftCardCountTooLarge()
    {
        $merchantRiskIndicator = new RiskInfo();

        $this->expectException(\InvalidArgumentException::class);

        $merchantRiskIndicator->setGiftCardCount(133);
    }

    public function testSeamlessMappingWithGiftCardCountTooSmall()
    {
        $merchantRiskIndicator = new RiskInfo();

        $this->expectException(\InvalidArgumentException::class);

        $merchantRiskIndicator->setGiftCardCount(0);
    }

    public function testSeamlessMappingWithAllFields()
    {
        $merchantRiskIndicator = new RiskInfo();
        $giftCardCount         = 13;
        $mail                  = 'max.muster@mail.com';
        $giftAmount            = new Amount(143.78, 'EUR');
        $date                  = new \DateTime();

        $expected = [
            'risk_info_delivery_timeframe'   => RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY,
            'risk_info_delivery_mail'        => $mail,
            'risk_info_reorder_items'        => RiskInfoReorder::FIRST_TIME_ORDERED,
            'risk_info_availability'         => RiskInfoAvailability::MERCHANDISE_AVAILABLE,
            'risk_info_preorder_date'        => $date->format(RiskInfo::DATE_FORMAT),
            'risk_info_gift_amount'          => 143,
            'risk_info_gift_amount_currency' => $giftAmount->getCurrency(),
            'risk_info_gift_card_count'      => $giftCardCount,
        ];

        $merchantRiskIndicator->setAvailability(RiskInfoAvailability::MERCHANDISE_AVAILABLE);
        $merchantRiskIndicator->setDeliveryEmailAddress($mail);
        $merchantRiskIndicator->setDeliveryTimeFrame(RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY);
        $merchantRiskIndicator->setGiftAmount($giftAmount);
        $merchantRiskIndicator->setGiftCardCount($giftCardCount);
        $merchantRiskIndicator->setPreOrderDate($date);
        $merchantRiskIndicator->setReorderItems(RiskInfoReorder::FIRST_TIME_ORDERED);

        $this->assertEquals($expected, $merchantRiskIndicator->mappedSeamlessProperties());
    }
}
