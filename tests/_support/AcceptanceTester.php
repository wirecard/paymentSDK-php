<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
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
use Page\CreditCardCreateUITokenize as CreditCardCreateUITokenizePage;
use Page\CreditCardCreateUIPaymentAction as CreditCardCreateUIPaymentActionPage;
use Page\CreditCardReserve as CreditCardReservePage;
use Page\CreditCardSuccess as CreditCardSuccessPage;
use Page\Verified as VerifiedPage;
use Page\CreditCardCancel as CreditCardCancelPage;
use Page\SimulatorPage as SimulatorPage;
use Page\CreditCardSuccessNon3D as CreditCardSuccessNon3DPage;
use Page\CreditCardPayBasedOnReserve as CreditCardPayBasedOnReservePage;
// WPPv2 3D
use Page\CreditCardCreateUIWppV2 as CreditCardCreateUIWppV2Page;
use Page\CreditCardCreateUIWppV2PaymentAction as CreditCardCreateUIWppV2PaymentActionPage;
// WPPv2 Non 3D
use Page\CreditCardCreateUINon3DWppV2 as CreditCardCreateUINon3DWppV2Page;
use Page\CreditCardCreateUINon3DWppV2PaymentAction as CreditCardCreateUINon3DWppV2PaymentActionPage;
use Page\CreditCardWppV2SuccessNon3D as CreditCardWppV2SuccessNon3DPage;
use Page\CreditCardReserveTokenize as CreditCardReserveTokenizePage;
use Page\WirecardTransactionDetails as WirecardTransactionDetailsPage;
//PayPal
use Page\PayPalLogin as PayPalLoginPage;
use Page\PayPalReview as PayPalReviewPage;
use Page\PayPalSuccess as PayPalSuccessPage;
use Page\PayPalPayBasedOnReserve as PayPalPayBasedOnReservePage;
use Page\PayPalCancel as PayPalCancelPage;
use Page\PayPalLoginPurchase as PayPalLoginPurchasePage;

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
            case "Create Credit Card UI WPPv2 Payment Action Page":
                $page = new CreditCardCreateUIWppV2PaymentActionPage($this);
                break;
            // Credit Card non 3D WPPv2
            case "Create Credit Card UI non 3D WPPv2 Page":
                $page = new CreditCardCreateUINon3DWppV2Page($this);
                break;
            case "Credit Card Success non 3D WPPv2 Page":
                $page = new CreditCardWppV2SuccessNon3DPage($this);
                break;
            case "Create Credit Card UI non 3D WPPv2 Payment Action Page":
                $page = new CreditCardCreateUINon3DWppV2PaymentActionPage($this);
                break;
            // Credit Card tokenize
            case "Create Credit Card UI Tokenize Page":
                $page = new CreditCardCreateUITokenizePage($this);
                break;
            case "Credit Card Reserve Page":
                $page = new CreditCardReservePage($this);
                break;
            case "Credit Card Tokenize Reserve Page":
                $page = new CreditCardReserveTokenizePage($this);
                break;
            case "Credit Card Success Page":
                $page = new CreditCardSuccessPage($this);
                break;
            case "Verified Page":
                $page = new VerifiedPage($this);
                break;
            case "Credit Card Cancel Page":
                $page = new CreditCardCancelPage($this);
                break;
            case "SimulatorPage":
                $page = new SimulatorPage($this);
                break;
            case "Credit Card Success Page Non 3D Page":
                $page = new CreditCardSuccessNon3DPage($this);
                break;
            case "Create Credit Card Pay Based On Reserve":
                $page = new CreditCardPayBasedOnReservePage($this);
                break;
            case "Create Credit Card UI Payment Action Page":
                $page = new CreditCardCreateUIPaymentActionPage($this);
                break;
            case "Pay Pal Log In":
                $page = new PayPalLoginPage($this);
                break;
            case "Pay Pal Review":
                $page = new PayPalReviewPage($this);
                break;
            case "Pay Pal Pay Based On Reserve":
                $page = new PayPalPayBasedOnReservePage($this);
                break;
            case "Pay Pal Success":
                $page = new PayPalSuccessPage($this);
                break;
            case "Pay Pal Cancel":
                $page = new PayPalCancelPage($this);
                break;
            case "Wirecard Transaction Details":
                $page = new WirecardTransactionDetailsPage($this);
                break;
            case "Pay Pal Log In Purchase":
                $page = new PayPalLoginPurchasePage($this);
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
        $this->amOnPage($this->currentPage->getURL($this->getScenario()->current('name')));
        $this->currentPage->waitUntilLoaded();
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
        $this->currentPage->waitUntilLoaded();
        $this->wait(3);
        $this->seeInCurrentUrl($this->getPageSpecific());
    }

    /**
     * @Then I see :element
     */
    public function iSee($element)
    {
        $this->waitForElementVisible($this->getPageElement($element), 30);
        $this->seeElement($this->getPageElement($element));
    }

    /**
     *  * @When I enter :fieldValue in field :fieldID
     */
    public function iEnterInField($fieldValue, $fieldID)
    {
        $this->waitForElementVisible($this->getPageElement($fieldID), 30);
        $fieldValueDefined = $this->currentPage->prepareDataForField($fieldValue, $this->valueToKeepBetweenSteps);
        $this->fillField($this->getPageElement($fieldID), $fieldValueDefined);
    }

    /**
     * @When I choose :fieldValue in field :fieldID
     */
    public function iChooseInField($fieldValue, $fieldID)
    {
        $this->waitForElementVisible($this->getPageElement($fieldID), 30);
        $this->selectOption($this->getPageElement($fieldID), $fieldValue);
    }

    /**
     * @When I click :object
     */
    public function iClick($object)
    {
        $this->currentPage->prepareClick();
        $this->waitForElementClickable($this->getPageElement($object), 60);
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

    /**
     * @Given I login to Paypal
     * @since 3.7.2
     */
    public function iLoginToPaypal()
    {
        $this->currentPage->performPaypalLogin();
    }

    /**
     * @Given I click :link link
     * @since 3.7.2
     */
    public function iClickLinkWithAuthCredentialsUserPassword($link)
    {
        $env = getenv('GATEWAY');
        $data_field_values = $this->getDataFromDataFile('tests/_data/gatewayUsers.json');
        $this->waitForElementVisible($this->getPageElement($link), 30);
        $link_address = $this->grabAttributeFrom($this->getPageElement($link), "href");
        $this->amOnUrl($this->formAuthLink(
            $link_address,
            $data_field_values->$env->username,
            $data_field_values->$env->password
        ));
    }

    /**
     * @Then I see in table key :tableKey value :tableValue
     * @since 3.7.2
     */
    public function iSeeInTableKeyValue($tableKey, $tableValue)
    {
        $this->currentPage->seeTransactionType($tableKey, $tableValue);
    }
}
