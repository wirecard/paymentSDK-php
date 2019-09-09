<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Helper;

/**
 * Class RequestInspector
 * @package Wirecard\PaymentSdk\Helper
 */
class RequestInspector
{
    const STATUS_NO_ACCESS = '403.1166';
    const STATUS_FIELD_CODE = 'code';

    /**
     * @param array $expectedStatusCodes
     * @param array $receivedStatusCodes
     * @return boolean
     * @since 3.9.0
     */
    public static function hasStatus($receivedStatusCodes, $expectedStatusCodes)
    {
        $intersection = array_intersect($expectedStatusCodes, $receivedStatusCodes);

        return count($intersection) == count($expectedStatusCodes);
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

        if (!array_key_exists('statuses', $request['payment'])) {
            return false;
        }

        $statuses = array_column(
            $request['payment']['statuses']['status'],
            self::STATUS_FIELD_CODE
        );

        return self::hasStatus(
            $statuses,
            [self::STATUS_NO_ACCESS]
        );
    }
}
