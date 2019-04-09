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

/**
 * Class AuthenticationInfo
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.7.0
 */
class AuthenticationInfo implements MappableEntity
{
    /**
     * @var string | enum
     */
    private $authMethod;

    /**
     * @var string | date
     */
    private $authTimestamp;

    /**
     * @var string | enum
     */
    private $authData;

    /**
     * @param $authMethod
     * @return $this
     */
    public function setAuthMethod($authMethod)
    {
        $this->authMethod = $authMethod;

        return $this;
    }

    /**
     * @param $authTimestamp
     * @return $this
     */
    public function setAuthTimestamp($authTimestamp)
    {
        $this->authTimestamp = $authTimestamp;

        return $this;
    }

    /**
     * @param $authData
     * @return $this
     */
    public function setAuthData($authData)
    {
        $this->authData = $authData;

        return $this;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $authenticationInfo = null;
        if (null !== $this->authMethod) {
            $authenticationInfo['threeDSReqAuthMethod'] = $this->authMethod;
        }

        if (null !== $this->authTimestamp) {
            $authenticationInfo['threeDSReqAuthTimestamp'] = $this->authTimestamp;
        }

        if (null !== $this->authData) {
            $authenticationInfo['threeDSReqAuthData'] = $this->authData;
        }

        return $authenticationInfo;
    }

    /**
     * @return array
     */
    public function mappedSeamlessProperties()
    {
        return $this->mappedProperties();
    }
}
