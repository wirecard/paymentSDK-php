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

use WhichBrowser;

/**
 * Class Device
 * @package Wirecard\PaymentSdk\Entity
 *
 * An immutable entity representing a device.
 */
class Device implements MappableEntity
{
    /**
     * @var string
     */
    private $fingerprint;


    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $operatingSystem;

    /**
     * @return string
     */
    public function getFingerprint()
    {
        return $this->fingerprint;
    }

    /**
     * @param string $fingerprint
     */
    public function setFingerprint($fingerprint)
    {
        $this->fingerprint = $fingerprint;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getOperatingSystem()
    {
        return $this->operatingSystem;
    }

    /**
     * @param string $operatingSystem
     */
    public function setOperatingSystem($operatingSystem)
    {
        $this->operatingSystem = $operatingSystem;
    }

    /**
     * Device constructor.
     * @param null $userAgentString usually $_SERVER['HTTP_USER_AGENT']
     */
    public function __construct($userAgentString = null)
    {
        if ($userAgentString) {
            $this->parseUserAgent($userAgentString);
        }
    }

    /**
     * parse and map device type and os from user agent string
     *
     * @param $userAgentString
     */
    protected function parseUserAgent($userAgentString)
    {
        $parser = new WhichBrowser\Parser($userAgentString);

        if (!$parser->isDetected()) {
            return;
        }

        if ($parser->isType('mobile')) {
            $this->setType('mobile');
        } elseif ($parser->isType('tablet')) {
            $this->setType('tablet');
        } elseif ($parser->isType('desktop')) {
            $this->setType('pc');
        } else {
            $this->setType('other');
        }

        if ($parser->isOs('Android')) {
            $this->setOperatingSystem('android');
        } elseif ($parser->isOs('iOS')) {
            $this->setOperatingSystem('ios');
        } elseif ($parser->isOs('Windows')) {
            $this->setOperatingSystem('windows');
        } elseif ($parser->isOs('Windows Phone')) {
            $this->setOperatingSystem('windows-mobile');
        } else {
            $this->setOperatingSystem('other');
        }
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $result = array();

        if (!is_null($this->fingerprint)) {
            $result['fingerprint'] = $this->fingerprint;
        }

        if (!is_null($this->type)) {
            $result['type'] = $this->type;
        }

        if (!is_null($this->operatingSystem)) {
            $result['operating-system'] = $this->operatingSystem;
        }
        return $result;
    }
}
