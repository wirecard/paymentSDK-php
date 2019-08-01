<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Constant\AuthMethod;
use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Entity\AccountInfo;

class AccountInfoUTest extends \PHPUnit_Framework_TestCase
{
    public function testSeamlessMappingWithAllFieldsForNewUser()
    {
        $accountInfo = new AccountInfo();

        $date                  = new \DateTime();

        $expected = [
            'authentication_method'        => AuthMethod::USER_CHECKOUT,
            'authentication_timestamp'     => $date->format(AccountInfo::DATE_FORMAT),
            'challenge_indicator'          => ChallengeInd::NO_PREFERENCE,
            'account_creation_date'        => $date->format(AccountInfo::DATE_FORMAT),
            'account_update_date'          => $date->format(AccountInfo::DATE_FORMAT),
            'account_password_change_date' => $date->format(AccountInfo::DATE_FORMAT),
            'shipping_address_first_use'   => $date->format(AccountInfo::DATE_FORMAT),
            'card_creation_date'           => $date->format(AccountInfo::DATE_FORMAT),
            'transactions_last_day'        => 0,
            'transactions_last_year'       => 0,
            'card_transactions_last_day'   => 0,
            'purchases_last_six_months'    => 0,
        ];

        $accountInfo->setAuthMethod(AuthMethod::USER_CHECKOUT);
        $accountInfo->setAuthTimestamp($date);
        $accountInfo->setChallengeInd(ChallengeInd::NO_PREFERENCE);
        $accountInfo->setCreationDate($date);
        $accountInfo->setUpdateDate($date);
        $accountInfo->setPassChangeDate($date);
        $accountInfo->setShippingAddressFirstUse($date);
        $accountInfo->setCardCreationDate($date);
        $accountInfo->setAmountTransactionsLastDay(0);
        $accountInfo->setAmountTransactionsLastYear(0);
        $accountInfo->setAmountCardTransactionsLastDay(0);
        $accountInfo->setAmountPurchasesLastSixMonths(0);

        $this->assertEquals($expected, $accountInfo->mappedSeamlessProperties());
    }

    public function testMappingWithAllFieldsForNewUser()
    {
        $accountInfo = new AccountInfo();

        $date                  = new \DateTime();

        $expected = [
            'authentication-method'        => AuthMethod::USER_CHECKOUT,
            'authentication-timestamp'     => $date->format(AccountInfo::DATE_FORMAT),
            'challenge-indicator'          => ChallengeInd::NO_PREFERENCE,
            'creation-date'                => $date->format(AccountInfo::DATE_FORMAT),
            'update-date'                  => $date->format(AccountInfo::DATE_FORMAT),
            'password-change-date'         => $date->format(AccountInfo::DATE_FORMAT),
            'shipping-address-first-use'   => $date->format(AccountInfo::DATE_FORMAT),
            'card-creation-date'           => $date->format(AccountInfo::DATE_FORMAT),
            'transactions-last-day'        => 0,
            'transactions-last-year'       => 0,
            'card-transactions-last-day'   => 0,
            'purchases-last-six-months'    => 0,
        ];

        $accountInfo->setAuthMethod(AuthMethod::USER_CHECKOUT);
        $accountInfo->setAuthTimestamp($date);
        $accountInfo->setChallengeInd(ChallengeInd::NO_PREFERENCE);
        $accountInfo->setCreationDate($date);
        $accountInfo->setUpdateDate($date);
        $accountInfo->setPassChangeDate($date);
        $accountInfo->setShippingAddressFirstUse($date);
        $accountInfo->setCardCreationDate($date);
        $accountInfo->setAmountTransactionsLastDay(0);
        $accountInfo->setAmountTransactionsLastYear(0);
        $accountInfo->setAmountCardTransactionsLastDay(0);
        $accountInfo->setAmountPurchasesLastSixMonths(0);

        $this->assertEquals($expected, $accountInfo->mappedProperties());
    }
}
