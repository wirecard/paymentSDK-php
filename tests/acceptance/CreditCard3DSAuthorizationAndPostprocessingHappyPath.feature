Feature: CreditCard3DSAuthorizationAndPostprocessingHappyPath
  As a user
  I want to make a purchase with a Credit Card 3DS
  And to see that transaction was successful
  And to be able to capture the transaction
  And to be able to refund the transaction
  And to be able to cancel the transaction

  Background:
    Given I am on "Create Credit Card UI Payment Action Page" page
    When I fill fields with "Valid Credit Card Data"
    And I enter "70" in field "Amount"
    And I choose "EUR" in field "Currency"
    And I click "Save"

  @API-TEST @API-WDCEE-TEST
  Scenario: authorization
    Given I am redirected to "Credit Card Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    When I am redirected to "Verified Page" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    And I am redirected to "Credit Card Success Page" page
    Then I see text "Payment successfully completed."
    And I see text "Transaction ID"
    And I click "Transaction Identification" link
    And I am redirected to "Wirecard Transaction Details" page
    And I see in table key "Transaction Type" value "authorization"

  @API-TEST @API-WDCEE-TEST
  Scenario: capture
    Given I am redirected to "Credit Card Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    And I am redirected to "Verified Page" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    And I am redirected to "Credit Card Success Page" page
    And I see text "Transaction ID"
    And I note the "Transaction Identification"
    When I am on "Create Credit Card Pay Based On Reserve" page
    And I enter "Noted Transaction Identification" in field "Reserved transaction ID"
    And I enter "70" in field "Amount"
    And I click "Pay"
    Then I see text "Payment successfully completed."
    And I see text "Transaction ID"

  @API-TEST @API-WDCEE-TEST
  Scenario: refund
    Given I am redirected to "Credit Card Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    And I am redirected to "Verified Page" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    And I am redirected to "Credit Card Success Page" page
    And I see text "Transaction ID"
    And I note the "Transaction Identification"
    When I am on "Create Credit Card Pay Based On Reserve" page
    And I enter "Noted Transaction Identification" in field "Reserved transaction ID"
    And I enter "70" in field "Amount"
    And I click "Pay"
    And I see text "Payment successfully completed."
    And I see text "Transaction ID"
    And I note the "Transaction Identification"
    Then I am on "Credit Card Cancel Page" page
    And I enter "Noted Transaction Identification" in field "Transaction ID to be refunded"
    And I enter "70" in field "Amount"
    And I choose "EUR" in field "Currency"
    And I click "Refund"
    And I see text "Payment successfully cancelled."
    And I see text "Transaction ID"

  @API-TEST @API-WDCEE-TEST
  Scenario: cancel
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
