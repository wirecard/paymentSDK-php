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
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */

use Helper\Acceptance;

class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    private $pages = array(
        "Create Credit Card UI Page" => "\Page\CreditCardCreateUIPage",
        "Credit Card Reserve Page" => "\Page\CreditCardReservePage",
        "Credit Card Success Page" => "\Page\CreditCardSuccessPage",
        "Verified by Visa Page" => "\Page\VerifiedByVisaPage",
        "Wirecard Transaction Details Page" => "\Page\WirecardTransactionDetailsPage",
        "Credit Card Cancel Page" => "\Page\CreditCardCancelPage"
    );

    private $currentPage;

    private $valueToKeepBetweenSteps = '';

    /**
     * @Given I am on :page page
     */
    public function iAmOnPage($page)
    {
        // Open the page and initialize required pageObject
        $this->currentPage = $this->pages[$page];

        $curPage = $this->currentPage;
        $this->amOnPage($curPage::$URL);
        if ($page == "Create Credit Card UI Page") {
            // Switch to Credit Card UI frame
            $wirecard_frame = "wirecard-seamless-frame";
            $this->executeJS('jQuery(".' . $wirecard_frame . '").attr("name", "' . $wirecard_frame . '")');
            $this->switchToIFrame("$wirecard_frame");
        }
    }

    /**
     * @Then I am redirected to :page page
     */
    public function iAmRedirectedToPage($page)
    {
        // Initialize required pageObject WITHOUT checking URL
        $this->currentPage = $this->pages[$page];
    }

    /**
     * @Then I see :element
     */
    public function iSee($element)
    {
        $this->waitForElementVisible($this->getPageElement($element));
        $this->seeElement($this->getPageElement($element));
    }

    private function getPageElement($elementName)
    {
        //Takes the required element by it's name from required page
        $curPage = $this->currentPage;
        return $curPage::getElement($elementName);
    }

    /**
     * @When In field :fieldID I enter :fieldValue
     */
    public function inFieldIEnter($fieldID, $fieldValue)
    {
        $this->waitForElementVisible($this->getPageElement($fieldID));
        if (strpos($fieldValue, "Noted") !== false) {
            $fieldValue = $this->valueToKeepBetweenSteps;
        }
        $this->fillField($this->getPageElement($fieldID), $fieldValue);
    }

    /**
     * @When In field :fieldID I choose :fieldValue
     */
    public function inFieldIChoose($fieldID, $fieldValue)
    {
        $this->waitForElementVisible($this->getPageElement($fieldID));
        $this->selectOption($this->getPageElement($fieldID), $fieldValue);
    }

    /**
     * @When I click :object
     */
    public function iClick($object)
    {
        if ($object == "Save") {
            $this->switchToIFrame();
        }
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
     * @Then I see in table key :tableKey value :tableValue
     */
    public function iSeeInTableKeyValue($tableKey, $tableValue)
    {
        $this->waitForElementVisible($this->getPageElement($tableKey));
        $this->waitForElementVisible($this->getPageElement($tableValue));
        $this->see($tableKey);
        $this->see($tableValue);
    }


    /**
     * @Given I click :link link with auth credentials user :username password :password
     */
    public function iClickLinkWithAuthCredentialsUserPassword($link, $username, $password)
    {
        $this->waitForElementVisible($this->getPageElement($link));
        //this will inject credentials directly to the URL to avoid dealing with popup
        $link_address = $this->grabAttributeFrom($this->getPageElement($link), "href");
        $this->amOnUrl($this->formAuthLink($link_address, $username, $password));
    }

    /**
     * @Given I note the :value
     */
    public function iNoteThe($value)
    {
        $link = $this->grabAttributeFrom($this->getPageElement($value), 'href');
        $this->valueToKeepBetweenSteps = $this->getTransactionIDFromLink($link);
    }
}
