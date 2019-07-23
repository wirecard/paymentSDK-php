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
 * Class Periodic
 * @package Wirecard\PaymentSdk\Entity
 * @since 2.1.6
 */
class Periodic implements MappableEntity
{
    /**
     * @var string
     */
    private $periodicType;

    /**
     * @var string
     */
    private $sequenceType;

    public function __construct($periodicType = null, $sequenceType = null)
    {
        if (null !== $periodicType) {
            $this->setPeriodicType($periodicType);
        }
        if (null !== $sequenceType) {
            $this->setSequenceType($sequenceType);
        }
    }

    /**
     * @return $this
     */
    public function setPeriodicType($periodicType)
    {
        if (!in_array($periodicType, ['ucof', 'ci', 'recurring', 'installment'])) {
            throw new \UnexpectedValueException("Periodic type '$periodicType' is not supported!");
        }
        $this->periodicType = $periodicType;
        return $this;
    }

    /**
     * @return $this
     */
    public function setSequenceType($sequenceType)
    {
        if (!in_array($sequenceType, ['final', 'first', 'recurring'])) {
            throw new \UnexpectedValueException("Sequence type '$sequenceType' is not supported!");
        }
        $this->sequenceType = $sequenceType;
        return $this;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $periodic = null;
        if (null !== $this->periodicType) {
            $periodic['periodic-type'] = $this->periodicType;
        }

        if (null !== $this->sequenceType) {
            $periodic['sequence-type'] = $this->sequenceType;
        }

        return $periodic;
    }

    /**
     * @return array
     */
    public function mappedSeamlessProperties()
    {
        $periodic = null;
        if (null !== $this->periodicType) {
            $periodic['periodic_type'] = $this->periodicType;
        }

        if (null !== $this->sequenceType) {
            $periodic['sequence_type'] = $this->sequenceType;
        }

        return $periodic;
    }
}
