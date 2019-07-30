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
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Constant\AuthMethod;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

/**
 * Class AuthenticationInfo
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.8.0
 */
class AuthenticationInfo implements MappableEntity
{
    /**
     * @var string DATE_FORMAT
     */
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
     * @var AuthMethod
     */
    private $authMethod;

    /**
     * @var \DateTime
     */
    private $authTimestamp;

    /**
     * @var ChallengeInd
     */
    private $challengeInd;

    /**
     * @param $authMethod
     * @return $this
     * @since 3.8.0
     */
    public function setAuthMethod($authMethod)
    {
        if (!AuthMethod::isValid($authMethod)) {
            throw new MandatoryFieldMissingException('Authentication method is not supported.');
        }

        $this->authMethod = $authMethod;

        return $this;
    }

    /**
     * @param $authTimestamp
     * @return $this
     * @since 3.8.0
     */
    public function setAuthTimestamp($authTimestamp = null)
    {
        if (null == $authTimestamp) {
            $authTimestamp = gmdate(self::DATE_FORMAT);
        }

        $this->authTimestamp = $authTimestamp;

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
        $authenticationInfo = array();
        if (null !== $this->authMethod) {
            $authenticationInfo['authentication_method'] = $this->authMethod;
        }

        if (null !== $this->authTimestamp) {
            $authenticationInfo['authentication_timestamp'] = $this->authTimestamp;
        }

        if (null !== $this->challengeInd) {
            $authenticationInfo['challenge_indicator'] = $this->challengeInd;
        }

        return $authenticationInfo;
    }
}
