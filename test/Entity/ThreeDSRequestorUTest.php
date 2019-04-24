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

    public function testMappingNotImplemented()
    {
        $threeDSRequestor = new ThreeDSRequestor();

        $this->expectException(NotImplementedException::class);
        $threeDSRequestor->mappedProperties();
    }
}
