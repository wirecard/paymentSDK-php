<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Mapper\Response;

use Wirecard\PaymentSdk\Response\Response;

/**
 * Interface MapperInterface
 * @package Wirecard\PaymentSdk\Mapper\Response
 * @since 4.0.0
 */
interface MapperInterface
{
    /**
     * @return Response
     * @since 4.0.0
     */
    public function map();
}
