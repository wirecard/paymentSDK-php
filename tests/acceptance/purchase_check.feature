Feature: check_credit_card_functionality
  As a user
  I want to make a purchase with a Credit Card
  And to see that transaction was successful
  And to be able to cancel the transaction

  Background:
    Given I am on "Create Credit Card UI Page" page
    When I enter "test" in field "Last name"
    And I enter "4012000300001003" in field "Card number"
    And I enter "003" in field "CVV"
    And I choose "01" in field "Valid until month"
    And I choose "2019" in field "Valid until year"
    And I click "Save"
    Then I am redirected to "Credit Card Reserve Page" page
    And I wait for 3 seconds
    And I click "Redirect to 3-D Secure page"
    Then I am redirected to "Verified by Visa Page" page
    And I wait for 5 seconds
    And I enter "wirecard" in field "Password" 
    And I click "Continue"
    And I wait for 5 seconds

#  Scenario: try purchase_check
#    Given I am redirected to "Credit Card Success Page" page
#    Then I see text "Payment successfully completed."
#    And I see text "Transaction ID"

  Scenario: try refund_check
    Given I am redirected to "Credit Card Success Page" page
    And I see text "Transaction ID"
    And I note the "Transaction Identification"
    When I am on "Credit Card Cancel Page" page
    And I enter "Noted Transaction Identification" in field "Transaction ID to be refunded"
    And I click "Refund"
    Then I see text "Payment successfully cancelled."
    And I see text "Transaction ID"
