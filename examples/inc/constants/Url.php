<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Example\Constants;

class Url
{
    const SUCCESS_URL = "return.php?status=success";
    const FAILURE_URL = "return.php?status=failure";
    const CANCEL_URL = "return.php?status=cancel";

    const NOTIFICATION_URL = "notify.php";
}
