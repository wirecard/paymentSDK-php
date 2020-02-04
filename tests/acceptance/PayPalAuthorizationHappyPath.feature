Feature: PayPalAuthorizationHappyPath
  As a user
  I want to make an authorization with a PayPal
  And to see that authorization was successful

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
