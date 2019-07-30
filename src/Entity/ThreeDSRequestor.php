<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

/**
 * Class ThreeDSRequestor
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.8.0
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
     * @since 3.8.0
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
     * @since 3.8.0
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
     * @return AuthenticationInfo
     * @since 3.8.0
     */
    public function getAuthenticationInfo()
    {
        return $this->authenticationInfo;
    }

    /**
     * @return ChallengeInd
     * @since 3.8.0
     */
    public function getChallengeInd()
    {
        return $this->challengeInd;
    }

    /**
     * @return array|void
     * @throws NotImplementedException
     * @since 3.8.0
     */
    public function mappedProperties()
    {
        throw new NotImplementedException('mappedProperties() not supported for this entity, 
        mappedSeamlessProperties() only.');
    }

    /**
     * @return array
     * @since 3.8.0
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
