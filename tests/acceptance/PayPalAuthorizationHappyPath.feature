Feature: PayPalAuthorizationHappyPath
  As a user
  I want to make an authorization with a PayPal
  And to see that reservation was successful

  Background:
    Given I am on "Pay Pal Log In" page

  @API-TEST @API-WDCEE-TEST
  Scenario: authorization
    Given I login to Paypal
    And I am redirected to "Pay Pal Review" page
    When I click "Pay Now"
    And I am redirected to "Pay Pal Success" page
    And I see text "Reservation successfully completed."
    And I see text "Transaction ID"
    Then I click "Transaction Identification" link
    And I am redirected to "Wirecard Transaction Details" page
    And I see in table key "Transaction Type" value "authorization"
