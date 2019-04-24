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

use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

/**
 * Class ThreeDSRequestor
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.7.0
 */
class ThreeDSRequestor implements MappableEntity
{
    /**
     * @var AuthenticationInfo
     */
    private $authenticationInfo;

    /**
     * @var ChallengeInd
     */
    private $challengeInd;

    /**
     * @param AuthenticationInfo $authenticationInfo
     * @return $this
     * @since 3.7.0
     */
    public function setAuthenticationInfo($authenticationInfo)
    {
        if (!$authenticationInfo instanceof AuthenticationInfo) {
            throw new \InvalidArgumentException(
                '3DS Requestor Authentication Information must be of type AuthenticationInfo.'
            );
        }
        $this->authenticationInfo = $authenticationInfo;

        return $this;
    }

    /**
     * @param string $challengeInd
     * @return $this
     * @since 3.7.0
     */
    public function setChallengeInd($challengeInd)
    {
        if (!ChallengeInd::isValid($challengeInd)) {
            throw new \InvalidArgumentException('Challenge indication preference is invalid.');
        }

        $this->challengeInd = $challengeInd;

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

    /**
     * @return array
     * @since 3.7.0
     */
    public function mappedSeamlessProperties()
    {
        $threeDSRequestor = array();
        if (null !== $this->authenticationInfo) {
            $threeDSRequestor = array_merge($threeDSRequestor, $this->authenticationInfo->mappedSeamlessProperties());
        }

        if (null !== $this->challengeInd) {
            $threeDSRequestor['challenge_indicator'] = $this->challengeInd;
        }

        return $threeDSRequestor;
    }
}
