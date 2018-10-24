Feature: check_credit_card_functionality
  As a user
  I want to make a purchase with a Credit Card
  And to see that transaction was successful
  And to be able to cancel the transaction

  Background:
    Given I am on "Create Credit Card UI Page" page
    When In field "Last name" I enter "test"
    And In field "Card number" I enter "4012000300001003"
    And In field "CVV" I enter "001"
    And In field "Valid until month" I choose "01"
    And In field "Valid until year" I choose "2019"
    And I click "Save"
    Then I am redirected to "Reserve Page" page
    And I wait for 3 seconds
    And I click "Redirect to 3-D Secure page"
    Then I am redirected to "Verified by Visa Page" page
    And In field "Password" I enter "wirecard"
    And I click "Continue"
    And I wait for 5 seconds
    Then I am redirected to "Success Page" page
    And I see text "Payment successfully completed"
    And I see text "Transaction ID"

  Scenario: try purchase_check
    Given I click "Transaction Identification" link with auth credentials user "70000-APITEST-AP" password "qD2wzQ_hrc!8"
    Then I am redirected to "Wirecard Transaction Details Page" page
    And I see in table key "Transaction State" value "SUCCESS"