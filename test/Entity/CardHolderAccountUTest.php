<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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
