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
 * @since 4.0.0
 */
class RequestInspector
{
    const STATUS_NO_ACCESS = '403.1166';

    const REQUEST_PAYMENT = 'payment';
    const REQUEST_STATUSES = 'statuses';
    const REQUEST_STATUS = 'status';

    /**
     * Checks that *all* expected status codes appear in a request
     *
     * @param array $expectedStatusCodes
     * @param array $receivedStatusCodes
     * @return boolean
     * @since 4.0.0
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
     * @since 4.0.0
     */
    public static function isValidRequest($request)
    {
        if (is_null($request)) {
            return false;
        }

        if (!array_key_exists(self::REQUEST_PAYMENT, $request)) {
            return false;
        }

        if (!array_key_exists(self::REQUEST_STATUSES, $request[self::REQUEST_PAYMENT])) {
            return false;
        }

        if (!array_key_exists(self::REQUEST_STATUS, $request[self::REQUEST_PAYMENT][self::REQUEST_STATUSES])) {
            return false;
        }

        $statuses = array_column(
            $request[self::REQUEST_PAYMENT][self::REQUEST_STATUSES],
            StatusFields::CODE
        );

        if (self::hasStatus(
            $statuses,
            [self::STATUS_NO_ACCESS]
        )) {
            return false;
        }

        return true;
    }
}
