<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\AuthenticationInfo;
use Wirecard\PaymentSdk\Entity\ThreeDSRequestor;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

class ThreeDSRequestorUTest extends \PHPUnit_Framework_TestCase
{
    public function testSeamlessMappingWithAuthenticationInfo()
    {
        $threeDSRequestor   = new ThreeDSRequestor();
        $authenticationInfo = new AuthenticationInfo();
        $timeStamp          = gmdate(AuthenticationInfo::DATE_FORMAT);

        $authenticationInfo->setAuthMethod(\Wirecard\PaymentSdk\Constant\AuthMethod::GUEST_CHECKOUT);
        $authenticationInfo->setAuthTimestamp($timeStamp);
        $threeDSRequestor->setAuthenticationInfo($authenticationInfo);

        $expected = [
            'authentication_method'    => \Wirecard\PaymentSdk\Constant\AuthMethod::GUEST_CHECKOUT,
            'authentication_timestamp' => $timeStamp,
        ];

        $this->assertEquals($expected, $threeDSRequestor->mappedSeamlessProperties());
    }

    public function testSeamlessMappingWithChallengeInd()
    {
        $threeDSRequestor = new ThreeDSRequestor();
        $threeDSRequestor->setChallengeInd(\Wirecard\PaymentSdk\Constant\ChallengeInd::NO_PREFERENCE);

        $expected = [
            'challenge_indicator' => \Wirecard\PaymentSdk\Constant\ChallengeInd::NO_PREFERENCE,
        ];

        $this->assertEquals($expected, $threeDSRequestor->mappedSeamlessProperties());
    }

    public function testSeamlessMappingWithAllFields()
    {
        $threeDSRequestor   = new ThreeDSRequestor();
        $authenticationInfo = new AuthenticationInfo();
        $timeStamp          = gmdate(AuthenticationInfo::DATE_FORMAT);

        $authenticationInfo->setAuthMethod(\Wirecard\PaymentSdk\Constant\AuthMethod::GUEST_CHECKOUT);
        $authenticationInfo->setAuthTimestamp($timeStamp);
        $threeDSRequestor->setAuthenticationInfo($authenticationInfo);

        $threeDSRequestor->setChallengeInd(\Wirecard\PaymentSdk\Constant\ChallengeInd::NO_PREFERENCE);

        $expected = [
            'authentication_method'    => \Wirecard\PaymentSdk\Constant\AuthMethod::GUEST_CHECKOUT,
            'authentication_timestamp' => $timeStamp,
            'challenge_indicator'      => \Wirecard\PaymentSdk\Constant\ChallengeInd::NO_PREFERENCE,
        ];

        $this->assertEquals($expected, $threeDSRequestor->mappedSeamlessProperties());
    }

    public function testMappingNotSupported()
    {
        $threeDSRequestor = new ThreeDSRequestor();

        $this->expectException(NotImplementedException::class);
        $threeDSRequestor->mappedProperties();
    }
}
