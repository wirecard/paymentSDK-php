<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class Base
{
    protected $URL = '';

    protected $elements = array();

    protected $tester;

    /**
     * @var AcceptanceTester
     */
    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    /**
     * Method getElement
     *
     * @param string $name
     * @return string
     */
    public function getElement($name)
    {
        return $this->elements[$name];
    }

    /**
     * Method getURL
     *
     * @return string
     */
    public function getURL()
    {
        return $this->URL;
    }

    /**
     * Method switchFrame
     */
    public function switchFrame()
    {
        ;
    }

    /**
     * Method prepareClick
     */
    public function prepareClick()
    {
        ;
    }

    /**
     * Method Method prepareDataForField
     * @param string $fieldValue
     * @param string $valueToKeepBetweenSteps
     * @return string
     */
    public function prepareDataForField($fieldValue, $valueToKeepBetweenSteps)
    {
        return $fieldValue;
    }
}
