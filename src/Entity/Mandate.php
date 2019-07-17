<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

class Mandate implements MappableEntity
{
    /**
     * @var string
     */
    private $id;

    /**
     * Mandate constructor.
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function mappedProperties()
    {
        return [
            'mandate-id' => $this->id,
            'signed-date' => gmdate('Y-m-d')
        ];
    }
}
