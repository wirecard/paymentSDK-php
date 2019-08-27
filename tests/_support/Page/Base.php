<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class Base
{
    //include url of current page
    protected $URL = '';

    //page elements
    protected $elements = array();

    //Acceptance tester instance
    protected $tester;

    //page specific text that can be found in the URL
    public $pageSpecific = '';

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
     * @param string $scenarioName
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
        return $this->pageSpecific;
    }

    /**
     * Method performPaypalLogin
     *
     * @since   3.7.2
     */
    public function performPaypalLogin()
    {
        ;
    }

    /**
     * Method waitUntilLoaded
     *
     * @since   3.8.0
     */
    public function waitUntilLoaded()
    {
        $I = $this->tester;
        $timeout = 40;
        $counter = 0;
        while ($counter <= $timeout) {
            $I->wait(1);
            $counter++;
            $currentUrl = $I->grabFromCurrentUrl();
            if ($currentUrl != '' && $this->getPageSpecific() != '') {
                if (strpos($currentUrl, $this->getPageSpecific()) != false) {
                    break;
                }
            }
        }
    }
}
