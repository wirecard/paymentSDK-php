<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */
namespace WirecardExample\Helpers;

use WirecardExample\Helpers\notifications\BaseNotificationHelper;
use WirecardExample\Helpers\notifications\EpsNotificationHelper;
use WirecardExample\Helpers\notifications\GiropayNotificationHelper;
use WirecardExample\Helpers\notifications\PaypalNotificationHelper;
use Exception;

/**
 * Class NotificationFactory
 * @package WirecardExample\Helpers
 * NotificationFactory is used for creating instances of NotificationHelper classes.
 */
class NotificationFactory
{
    /**
     * Create notification helper
     * @param string $type
     * @param string $payload
     * @param string $useFormat
     * @return BaseNotificationHelper
     * @throws Exception
     */
    public function createNotificationHelper(
        $type,
        $payload = "",
        $useFormat = BaseNotificationHelper::FORMAT_PAYLOAD_BASE64_ENCODED_XML
    ) {
        switch ($type) {
            case NotificationType::TYPE_EPS:
                return new EpsNotificationHelper($payload, $useFormat);
            case NotificationType::TYPE_GIROPAY:
                return new GiropayNotificationHelper($payload, $useFormat);
            case NotificationType::TYPE_PAYPAL:
                return new PaypalNotificationHelper($payload, $useFormat);
            default:
                throw new Exception("Unknown notification helper type: {$type}");
        }
    }
}
