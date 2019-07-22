Feature: CreditCard3DSPurchaseAndPostprocessingHappyPath
  As a user
  I want to make a purchase with a Credit Card 3DS
  And to see that transaction was successful
  And to be able to cancel/refund the transaction

  Background:
    Given I am on "Create Credit Card UI Payment Action Page" page
    When I fill fields with "Valid Credit Card Data"
    And I enter "70" in field "Amount"
    And I choose "EUR" in field "Currency"
    And I click "Save"

  @API-TEST @API-WDCEE-TEST
  Scenario: purchase
    Given I am redirected to "Credit Card Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    When I am redirected to "Verified Page" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    And I am redirected to "Credit Card Success Page" page
    Then I see text "Payment successfully completed."
    And I see text "Transaction ID"

  @API-TEST @API-WDCEE-TEST
  Scenario: refundOrcancel
    Given I am redirected to "Credit Card Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    And I am redirected to "Verified Page" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    And I am redirected to "Credit Card Success Page" page
    And I see text "Transaction ID"
    And I note the "Transaction Identification"
    When I am on "Credit Card Cancel Page" page
    And I enter "Noted Transaction Identification" in field "Transaction ID to be refunded"
    And I enter "70" in field "Amount"
    And I choose "EUR" in field "Currency"
    And I click "Refund"
    Then I see text "Payment successfully cancelled."
    And I see text "Transaction ID"
