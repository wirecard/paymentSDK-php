<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

/**
 * Class FormFieldMap
 * @package Wirecard\PaymentSdk\Entity
 */
class FormFieldMap implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $formFields = [];

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->formFields);
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function add($key, $value)
    {
        $this->formFields[$key] = $value;

        return $this;
    }
}
