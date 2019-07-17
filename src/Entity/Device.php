<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
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
