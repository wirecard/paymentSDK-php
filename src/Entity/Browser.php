<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

/**
 * Class Browser
 * @package Wirecard\PaymentSdk\Entity
 * @since 2.2.0
 */
class Browser implements MappableEntity
{
    /**
     * @var string $accept
     */
    protected $accept;

    /**
     * @var string $userAgent
     */
    protected $userAgent;

    /**
     * @var string $timezone
     */
    protected $timezone;

    /**
     * @var string $screenResolution
     */
    protected $screenResolution;

    /**
     * @var string $challengeWindowSize
     */
    private $challengeWindowSize;

    /**
     * Browser constructor.
     * @param null $accept
     * @param null $userAgent
     */
    public function __construct($accept = null, $userAgent = null)
    {
        if (!is_null($accept)) {
            $this->setAccept($accept);
        }
        if (!is_null($userAgent)) {
            $this->setUserAgent($userAgent);
        }
    }

    /**
     * @param $accept
     * @return $this
     */
    public function setAccept($accept)
    {
        $this->accept = $accept;
        return $this;
    }

    /**
     * @param $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @param $timezone
     * @return $this
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @param $screenResolution
     * @return $this
     */
    public function setScreenResolution($screenResolution)
    {
        $this->screenResolution = $screenResolution;
        return $this;
    }

    /**
     * @param int $width Width in pixel
     * @return $this
     * @since 3.8.0
     */
    public function setChallengeWindowSize($width)
    {
        switch (true) {
            case $width <= 250:
                $challengeWindowSize = '01'; // 250x400
                break;
            case $width <= 390:
                $challengeWindowSize = '02'; // 390x400
                break;
            case $width <= 500:
                $challengeWindowSize = '03'; // 500x600
                break;
            case $width <= 600:
                $challengeWindowSize = '04'; // 600x400
                break;
            default:
                $challengeWindowSize = '05'; // Fullscreen
        }

        $this->challengeWindowSize = $challengeWindowSize;

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

        if (isset($this->timezone)) {
            $data['time-zone'] = $this->timezone;
        }

        if (isset($this->screenResolution)) {
            $data['screen-resolution'] = $this->screenResolution;
        }

        if (isset($this->challengeWindowSize)) {
            $data['challenge-window-size'] = $this->challengeWindowSize;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function mappedSeamlessProperties()
    {
        $data = [];

        if (strlen($this->accept)) {
            $data['accept'] = $this->accept;
        } elseif (isset($_SERVER['HTTP_ACCEPT'])) {
            $data['accept'] = $_SERVER['HTTP_ACCEPT'];
        }

        if (strlen($this->userAgent)) {
            $data['user_agent'] = $this->userAgent;
        } elseif (isset($_SERVER['HTTP_USER_AGENT'])) {
            $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if (isset($this->timezone)) {
            $data['time_zone'] = $this->timezone;
        }

        if (isset($this->screenResolution)) {
            $data['screen_resolution'] = $this->screenResolution;
        }

        if (isset($this->challengeWindowSize)) {
            $data['browser_challenge_window_size'] = $this->challengeWindowSize;
        }

        return $data;
    }
}
