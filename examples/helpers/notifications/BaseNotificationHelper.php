<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */
namespace WirecardExample\Helpers\notifications;

/**
 * Class BaseNotificationHelper
 * @package WirecardExample\Helpers\notifications
 */
abstract class BaseNotificationHelper
{
    const FORMAT_PAYLOAD_BASE64_ENCODED_XML = "payload_base64";
    const FORMAT_PAYLOAD_XML = "payload_xml";

    /**
     * @var string
     */
    private $payload;


    /**
     * @var string|null
     */
    private $payloadFormat = self::FORMAT_PAYLOAD_BASE64_ENCODED_XML;

    /**
     * BaseNotificationHelper constructor.
     * @param $payload
     * @param null|string $payloadFormat
     * @throws \Exception
     */
    public function __construct($payload, $payloadFormat = null)
    {
        $this->payload = $payload;
        if (!is_null($payloadFormat)) {
            $this->payloadFormat = $payloadFormat;
        }
        $this->handlePayload();
    }

    /**
     * Define payload according inputs
     * @return void
     * @throws \Exception
     */
    protected function handlePayload()
    {
        if (empty($this->payload)) {
            switch ($this->payloadFormat) {
                case self::FORMAT_PAYLOAD_BASE64_ENCODED_XML:
                    $this->payload = $this->getNotificationAsBase64();
                    break;
                case self::FORMAT_PAYLOAD_XML:
                    $this->payload = $this->getNotificationAsXML();
                    break;
                default:
                    throw new \Exception("unsupported payload format: {$this->payloadFormat}");
            }
        }
    }

    /**
     * Get type
     * @return string
     */
    abstract public function getType();

    /**
     * Get payload
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Get base path to notifications
     * @return string
     */
    protected function getBasePath()
    {
        return dirname(__FILE__) . "/../../inc/notifications";
    }

    /**
     * Get base path to config for related notification object
     * @return string
     */
    protected function getNotificationBasePath()
    {
        return $this->getBasePath() . '/' . $this->getType();
    }

    /**
     * Get data in base64 encoded format for related notification
     * @return string
     */
    protected function getNotificationAsBase64()
    {
        return "";
    }

    /**
     * Get sample data in XML format for related notification
     * @return string
     */
    protected function getNotificationAsXML()
    {
        return "";
    }
}