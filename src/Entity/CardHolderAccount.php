<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Exception\NotImplementedException;

/**
 * Class CardHolderAccount
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.8.0
 */
class CardHolderAccount implements MappableEntity
{
    /**
     * @var string
     */
    private $merchantCrmId;

    /**
     * @param string $merchantCrmId
     * @return $this
     * @since 3.8.0
     */
    public function setMerchantCrmId($merchantCrmId)
    {
        if (mb_strlen((string)$merchantCrmId) > 64) {
            throw new \InvalidArgumentException('Max length for the crm id is 64.');
        }
        $this->merchantCrmId = $merchantCrmId;

        return $this;
    }


    /**
     * @return array|void
     * @throws NotImplementedException
     * @since 3.8.0
     */
    public function mappedProperties()
    {
        throw new NotImplementedException('mappedProperties() not supported for this entity, 
        mappedSeamlessProperties() only.');
    }
}
