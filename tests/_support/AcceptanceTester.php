<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */

use Helper\Acceptance;
use Page\Base;
use Page\CreditCardCreateUI as CreditCardCreateUIPage;
use Page\CreditCardReserve as CreditCardReservePage;
use Page\CreditCardSuccess as CreditCardSuccessPage;
use Page\Verified as VerifiedPage;
use Page\CreditCardCancel as CreditCardCancelPage;
use Page\SimulatorPage as SimulatorPage;
use Page\CreditCardSuccessNon3D as CreditCardSuccessNon3DPage;
// WPPv2 3D
use Page\CreditCardCreateUIWppV2 as CreditCardCreateUIWppV2Page;
use Page\CreditCardReserveWppV2 as CreditCardReserveWppV2Page;
// WPPv2 Non 3D
use Page\CreditCardCreateUINon3DWppV2 as CreditCardCreateUINon3DWppV2Page;
use Page\CreditCardWppV2SuccessNon3D as CreditCardWppV2SuccessNon3DPage;

class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    private $currentPage;

    private $valueToKeepBetweenSteps = '';

    /**
     * Method selectPage
     *
     * @param string $name
     * @return Base
     */
    private function selectPage($name)
    {
        $page = null;
        switch ($name) {
            // Credit Card WPPv2 3D
            case "Create Credit Card UI WPPv2 Page":
                $page = new CreditCardCreateUIWppV2Page($this);
                break;
            case "Credit Card Reserve WPPv2 Page":
                $page = new CreditCardReserveWppV2Page($this);
                $this->wait(25);
                break;
            // Credit Card non 3D WPPv2
            case "Create Credit Card UI non 3D WPPv2 Page":
                $page = new CreditCardCreateUINon3DWppV2Page($this);
                break;
            case "Credit Card Success non 3D WPPv2 Page":
                $page = new CreditCardWppV2SuccessNon3DPage($this);
                $this->wait(15);
                break;
            // Credit Card tokenize
            case "Create Credit Card UI Page":
                $page = new CreditCardCreateUIPage($this);
                break;
            case "Credit Card Reserve Page":
                $page = new CreditCardReservePage($this);
                $this->wait(15);
                break;
            case "Credit Card Success Page":
                $page = new CreditCardSuccessPage($this);
                $this->wait(10);
                break;
            case "Verified Page":
                $page = new VerifiedPage($this);
                $this->wait(5);
                break;
            case "Credit Card Cancel Page":
                $page = new CreditCardCancelPage($this);
                break;
            case "SimulatorPage":
                $page = new SimulatorPage($this);
                $this->wait(2);
                break;
            case "Credit Card Success Page Non 3D Page":
                $page = new CreditCardSuccessNon3DPage($this);
                $this->wait(15);
                break;
        }
        return $page;
    }

    /**
     * Method getPageElement
     *
     * @param string $elementName
     * @return string
     */
    private function getPageElement($elementName)
    {
        //Takes the required element by it's name from required page
        return $this->currentPage->getElement($elementName);
    }

    /**
     * Method getPageSpecific
     *
     * @return string
     */
    private function getPageSpecific()
    {
        //Returns pageSpecific property of the page
        return $this->currentPage->getPageSpecific();
    }

    /**
     * @Given I am on :page page
     */
    public function iAmOnPage($page)
    {
        // Open the page and initialize required pageObject
        $this->currentPage = $this->selectPage($page);
        $this->amOnPage($this->currentPage->getURL());
        $this->currentPage->switchFrame();
    }

    /**
     * @Then I am redirected to :page page
     */
    public function iAmRedirectedToPage($page)
    {
        // Initialize required pageObject WITHOUT checking URL
        $this->currentPage = $this->selectPage($page);
        // Check only specific keyword that page URL should contain
        $this->seeInCurrentUrl($this->getPageSpecific());
    }

    /**
     * @Then I see :element
     */
    public function iSee($element)
    {
        $this->waitForElementVisible($this->getPageElement($element));
        $this->seeElement($this->getPageElement($element));
    }

    /**
     *  * @When I enter :fieldValue in field :fieldID
     */
    public function iEnterInField($fieldValue, $fieldID)
    {
        $this->waitForElementVisible($this->getPageElement($fieldID));
        $fieldValueDefined = $this->currentPage->prepareDataForField($fieldValue, $this->valueToKeepBetweenSteps);
        $this->fillField($this->getPageElement($fieldID), $fieldValueDefined);
    }

    /**
     * @When I choose :fieldValue in field :fieldID
     */
    public function iChooseInField($fieldValue, $fieldID)
    {
        $this->waitForElementVisible($this->getPageElement($fieldID));
        $this->selectOption($this->getPageElement($fieldID), $fieldValue);
    }

    /**
     * @When I click :object
     */
    public function iClick($object)
    {
        $this->currentPage->prepareClick();
        $this->waitForElementVisible($this->getPageElement($object));
        $this->click($this->getPageElement($object));
    }

    /**
     * @Then I see text :text
     */
    public function iSeeText($text)
    {
        $this->see($text);
    }

    /**
     * @When I wait for :seconds seconds
     */
    public function iWaitForSeconds($seconds)
    {
        $this->wait($seconds);
    }

    /**
     * @Given I note the :value
     */
    public function iNoteThe($value)
    {
        $link = $this->grabAttributeFrom($this->getPageElement($value), 'href');
        $this->valueToKeepBetweenSteps = $this->getTransactionIDFromLink($link);
    }
    /**
     * @When I fill fields with :cardData
     */
    public function iFillFieldsWith($cardData)
    {
        $this->currentPage->fillCreditCardFields($cardData);
    }
}
