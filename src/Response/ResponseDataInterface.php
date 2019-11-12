<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Response;

use Wirecard\PaymentSdk\Entity\FormFieldMap;

/**
 * Interface ResponseDataInterface
 * @package Wirecard\PaymentSdk\Response
 * @since 4.0.0
 */
interface ResponseDataInterface
{
    const SUCCESS = 'success';
    const FAILURE = 'failure';
    const FORM_INTERACTION = 'form-interaction';

    /**
     * @return string
     * @since 4.0.0
     */
    public function getResponseType();

    /**
     * @return string
     * @since 4.0.0
     */
    public function getDataType();

    /**
     * @return \SimpleXMLElement
     * @since 4.0.0
     */
    public function getData();

    /**
     * @return FormFieldMap
     * @since 4.0.0
     */
    public function getFormFields();

    /**
     * @return string
     * @since 4.0.0
     */
    public function getUrl();
}
