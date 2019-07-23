<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Response;

/**
 * Class FailureResponse
 * @package Wirecard\PaymentSdk\Response
 *
 * An object representing a negative response from the payment provider.
 */
class FailureResponse extends Response
{
    protected function setValueForRequestId()
    {
        // Nothing to do.
        // If the response is a failure, we can not set the request ID.
    }
}
