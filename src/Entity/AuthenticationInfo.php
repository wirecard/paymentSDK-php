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

use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Constant\AuthMethod;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

/**
 * Class AuthenticationInfo
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.7.0
 */
class AuthenticationInfo implements MappableEntity
{
    /**
     * @var string DATE_FORMAT
     */
    const DATE_FORMAT = 'YmdHi';

    /**
     * @var AuthMethod
     */
    private $authMethod;

    /**
     * @var \DateTime
     */
    private $authTimestamp;

    /**
     * @param $authMethod
     * @return $this
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
     * @return array|void
     * @throws NotImplementedException
     */
    public function mappedProperties()
    {
        throw new NotImplementedException('mappedProperties() not implemented.');
    }

    /**
     * @return array
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

        return $authenticationInfo;
    }
}
