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

    public $page_specific = '';

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
    public function getURL($scenarioName)
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
     * Method prepareDataForField
     *
     * @param string $fieldValue
     * @param string $valueToKeepBetweenSteps
     * @return string
     */
    public function prepareDataForField($fieldValue, $valueToKeepBetweenSteps)
    {
        if (strpos($fieldValue, "Noted") !== false) {
            return $valueToKeepBetweenSteps;
        } else {
            return $fieldValue;
        }
    }

    /**
     * Method getPageSpecific
     *
     * @return string
     */
    public function getPageSpecific()
    {
        return $this->page_specific;
    }
}
