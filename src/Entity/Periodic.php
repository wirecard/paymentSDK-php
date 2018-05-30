<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
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
