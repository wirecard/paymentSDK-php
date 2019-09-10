<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Helper;
use Wirecard\PaymentSdk\Constant\StatusFields;

/**
 * Class RequestInspector
 * @package Wirecard\PaymentSdk\Helper
 * @since 3.9.0
 */
class RequestInspector
{
    const STATUS_NO_ACCESS = '403.1166';

    /**
     * Checks that *all* expected status codes appear in a request
     *
     * @param array $expectedStatusCodes
     * @param array $receivedStatusCodes
     * @return boolean
     * @since 3.9.0
     */
    public static function hasStatus($receivedStatusCodes, $expectedStatusCodes)
    {
        $intersection = array_intersect($expectedStatusCodes, $receivedStatusCodes);

        return count($intersection) === count($expectedStatusCodes);
    }

    /**
     * Checks if the request is null or if it contains an undesired status
     *
     * @param $request
     * @return bool
     * @since 3.9.0
     */
    public static function isValidRequest($request)
    {
        if (is_null($request)) {
            return false;
        }

        if (!array_key_exists('payment', $request)) {
            return false;
        }

        if (!array_key_exists('statuses', $request['payment'])) {
            return false;
        }

        if (!array_key_exists('status', $request['payment']['statuses'])) {
            return false;
        }

        $statuses = array_column(
            $request['payment']['statuses']['status'],
            StatusFields::CODE
        );

        return self::hasStatus(
            $statuses,
            [self::STATUS_NO_ACCESS]
        );
    }
}
