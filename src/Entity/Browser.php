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


class Browser implements MappableEntity
{
    protected $accept;
    protected $userAgent;

    /**
     * Browser constructor.
     * @param null $accept
     * @param null $userAgent
     */
    public function __construct($accept = null, $userAgent = null)
    {
        if (null !== $accept) {
            $this->setAccept($accept);
        }
        if (null !== $userAgent) {
            $this->setUserAgent($userAgent);
        }
    }

    public function setAccept($accept)
    {
        $this->accept = $accept;
        return $this;
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $data = [];

        if (strlen($this->accept)) {
            $data['accept'] = $this->accept;
        } elseif (isset($_SERVER['HTTP_ACCEPT'])) {
            $data['accept'] = $_SERVER['HTTP_ACCEPT'];
        }

        if (strlen($this->userAgent)) {
            $data['user-agent'] = $this->userAgent;
        } elseif (isset($_SERVER['HTTP_USER_AGENT'])) {
            $data['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        return $data;
    }
}