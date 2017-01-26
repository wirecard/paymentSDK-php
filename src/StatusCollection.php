<?php

namespace Wirecard\PaymentSdk;

use Traversable;

/**
 * Class StatusCollection
 * @package Wirecard\PaymentSdk
 */
class StatusCollection implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $statuses;

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

    public function add(Status $status)
    {
        $this->statuses[] = $status;
    }
}
