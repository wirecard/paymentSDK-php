<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Formatter;

/**
 * @package Wirecard\PaymentSdk\Formatter
 *
 * Implementation of a PropertyFormatter
 * It can be used to format Dates
 */
class DateFormatter implements PropertyFormatter
{
    const FORMATTER_NAME = 'dateFormatter';

    const PARAM_DATE_FORMAT_KEY = 'dateFormat';
    /**
     * @const string Default date format
     */
    const DATE_FORMAT = 'Ymd';

    /**
     * @param \DateTime $date
     * @param array $params
     * @return mixed
     */
    public function formatProperty($date, $params)
    {
        if (isset($params[self::PARAM_DATE_FORMAT_KEY])) {
            return $this->formatDateWithDateFormat($date, $params[self::PARAM_DATE_FORMAT_KEY]);
        }

        return $this->formatDateWithDateFormat($date, self::DATE_FORMAT);
    }

    /**
     * @param \DateTime $date
     * @param $format
     * @return mixed
     */
    private function formatDateWithDateFormat($date, $format)
    {
        return $date->format($format);
    }
}
