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

use Wirecard\PaymentSdk\Entity\CardHolderAccount;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

class CardHolderAccountUTest extends \PHPUnit_Framework_TestCase
{
    public function testSeamlessMappingWithCrmIdShort()
    {
        $cardHolderAccount = new CardHolderAccount();
        $crmId             = 'Standard_Crm_Id_123';

        $cardHolderAccount->setMerchantCrmId($crmId);
        $expected = [
            'merchant_crm_id' => $crmId,
        ];

        $this->assertEquals($expected, $cardHolderAccount->mappedSeamlessProperties());
    }

    public function testSeamlessMappingWithCrmIdLong()
    {
        $cardHolderAccount = new CardHolderAccount();
        $crmId = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut';

        $this->expectException(\InvalidArgumentException::class);

        $cardHolderAccount->setMerchantCrmId($crmId);
    }

    public function testSeamlessMappingWithAllFields()
    {
        $cardHolderAccount       = new CardHolderAccount();
        $crmId                   = 'Standard_Crm_Id_123';
        $date                    = new \DateTime();
        $transactionsLastDay     = 2;
        $transactionsLastYear    = 500;
        $cardTransactionsLastDay = 1;
        $purchasesLastSixMonths  = 30;

        $expected = [
            'account_creation_date'        => $date->format(CardHolderAccount::DATE_FORMAT),
            'account_update_date'          => $date->format(CardHolderAccount::DATE_FORMAT),
            'account_password_change_date' => $date->format(CardHolderAccount::DATE_FORMAT),
            'shipping_address_first_use'   => $date->format(CardHolderAccount::DATE_FORMAT),
            'card_creation_date'           => $date->format(CardHolderAccount::DATE_FORMAT),
            'transactions_last_day'        => $transactionsLastDay,
            'transactions_last_year'       => $transactionsLastYear,
            'card_transactions_last_day'   => $cardTransactionsLastDay,
            'purchases_last_six_months'    => $purchasesLastSixMonths,
            'suspicious_activity'          => '01',
            'merchant_crm_id'              => $crmId,
        ];

        $cardHolderAccount->setCreationDate($date);
        $cardHolderAccount->setUpdateDate($date);
        $cardHolderAccount->setPassChangeDate($date);
        $cardHolderAccount->setShippingAddressFirstUse($date);
        $cardHolderAccount->setCardCreationDate($date);
        $cardHolderAccount->setAmountTransactionsLastDay($transactionsLastDay);
        $cardHolderAccount->setAmountTransactionsLastYear($transactionsLastYear);
        $cardHolderAccount->setAmountCardTransactionsLastDay($cardTransactionsLastDay);
        $cardHolderAccount->setAmountPurchasesLastSixMonths($purchasesLastSixMonths);
        $cardHolderAccount->setSuspiciousActivity(false);
        $cardHolderAccount->setMerchantCrmId($crmId);

        $this->assertEquals($expected, $cardHolderAccount->mappedSeamlessProperties());
    }

    public function testMappingNotSupported()
    {
        $cardHolderAccount = new CardHolderAccount();

        $this->expectException(NotImplementedException::class);
        $cardHolderAccount->mappedProperties();
    }
}
