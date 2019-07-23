<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

use Traversable;

/**
 * Class StatusCollection
 * @package Wirecard\PaymentSdk\Entity
 */
class StatusCollection implements \IteratorAggregate
{
    /**
     * @var Status[]
     */
    private $statuses = [];

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->statuses);
    }

    /**
     * @param Status $status
     * @return $this
     */
    public function add(Status $status)
    {
        $this->statuses[] = $status;

        return $this;
    }

    /**
     * @param array $codes
     * @return boolean
     */
    public function hasStatusCodes(array $codes)
    {
        foreach ($this->statuses as $status) {
            if (in_array($status->getCode(), $codes, false)) {
                return true;
            }
        }

        return false;
    }
}
