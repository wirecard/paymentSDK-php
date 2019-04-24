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

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Constant\IsoTransactionType;
use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\RiskInfoDeliveryTimeFrame;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\MerchantRiskIndicator;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

class MerchantRiskIndicatorUTest extends \PHPUnit_Framework_TestCase
{
    public function testSeamlessMappingWithGiftCardCountInvalid()
    {
        $merchantRiskIndicator = new MerchantRiskIndicator();

        $this->expectException(\InvalidArgumentException::class);

        $merchantRiskIndicator->setGiftCardCount(133);
    }

    public function testSeamlessMappingWithAllFields()
    {
        $merchantRiskIndicator = new MerchantRiskIndicator();
        $giftCardCount         = 13;
        $mail                  = 'max.muster@mail.com';
        $giftAmount            = new Amount(143.78, 'EUR');
        $date                  = new \DateTime();

        $expected = [
            'risk_info_delivery_timeframe'   => RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY,
            'risk_info_delivery_mail'        => $mail,
            'risk_info_reorder_items'        => RiskInfoReorder::FIRST_TIME_ORDERED,
            'risk_info_availability'         => RiskInfoAvailability::MERCHANDISE_AVAILABLE,
            'risk_info_preorder_date'        => $date->format(MerchantRiskIndicator::DATE_FORMAT),
            'risk_info_gift_amount'          => 143,
            'risk_info_gift_amount_currency' => $giftAmount->getCurrency(),
            'risk_info_gift_card_count'      => $giftCardCount,
            'iso_transaction_type'           => IsoTransactionType::CHECK_ACCEPTANCE,
        ];

        $merchantRiskIndicator->setAvailability(RiskInfoAvailability::MERCHANDISE_AVAILABLE);
        $merchantRiskIndicator->setDeliveryEmailAddress($mail);
        $merchantRiskIndicator->setDeliveryTimeFrame(RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY);
        $merchantRiskIndicator->setGiftAmount($giftAmount);
        $merchantRiskIndicator->setGiftCardCount($giftCardCount);
        $merchantRiskIndicator->setIsoTransactionType(IsoTransactionType::CHECK_ACCEPTANCE);
        $merchantRiskIndicator->setPreOrderDate($date);
        $merchantRiskIndicator->setReorderItems(RiskInfoReorder::FIRST_TIME_ORDERED);

        $this->assertEquals($expected, $merchantRiskIndicator->mappedSeamlessProperties());
    }

    public function testMappingNotImplemented()
    {
        $cardHolderAccount = new MerchantRiskIndicator();

        $this->expectException(NotImplementedException::class);
        $cardHolderAccount->mappedProperties();
    }
}
