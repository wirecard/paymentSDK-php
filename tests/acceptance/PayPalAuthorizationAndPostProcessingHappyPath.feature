Feature: PayPalAuthorizationAndPostProcessingHappyPath
  As a user
  I want to make an authorization with a PayPal
  And to see that transaction was successful
  And to be able to capture the transaction
  And to be able to refund the transaction
  And to be able to cancel the transaction

  Background:
    Given I am on "Pay Pal Log In" page
    And I login to Paypal
    When I am redirected to "Pay Pal Review" page
    Then I click "Continue"
    Then I click "Pay Now"
    And I am redirected to "Pay Pal Success" page

  @API-TEST @API-WDCEE-TEST
  Scenario: authorization
    Given I see text "Reservation successfully completed."
    And I see text "Transaction ID"
    When I click "Transaction Identification" link
    Then I am redirected to "Wirecard Transaction Details" page
    And I see in table key "Transaction Type" value "authorization"

  @API-TEST @API-WDCEE-TEST
  Scenario: capture
    Given I see text "Transaction ID"
    And I note the "Transaction Identification"
    When I am on "Pay Pal Pay Based On Reserve" page
    And I enter "Noted Transaction Identification" in field "Reserved transaction ID"
    And I enter "12.59" in field "Amount"
    And I click "Pay"
    Then I see text "Payment successfully completed."
    And I see text "Transaction ID"

  @API-TEST @API-WDCEE-TEST
  Scenario: refund
    Given I see text "Transaction ID"
    And I note the "Transaction Identification"
    When I am on "Pay Pal Pay Based On Reserve" page
    And I enter "Noted Transaction Identification" in field "Reserved transaction ID"
    And I enter "12.59" in field "Amount"
    And I click "Pay"
    Then I see text "Payment successfully completed."
    And I see text "Transaction ID"
    And I note the "Transaction Identification"
    Then I am on "Pay Pal Cancel" page
    And I enter "Noted Transaction Identification" in field "Transaction ID to be refunded"
    And I click "Cancel"
    And I see text "Payment successfully cancelled."
    And I see text "Transaction ID"

  @API-TEST @API-WDCEE-TEST
  Scenario: cancel
    Given I see text "Transaction ID"
    And I note the "Transaction Identification"
    When I am on "Pay Pal Cancel" page
    And I enter "Noted Transaction Identification" in field "Transaction ID to be refunded"
    And I click "Cancel"
    Then I see text "Payment successfully cancelled."
    And I see text "Transaction ID"
