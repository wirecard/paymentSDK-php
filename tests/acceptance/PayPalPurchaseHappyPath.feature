Feature: PayPalPurchaseHappyPath
  As a user
  I want to make a purchase with a PayPal
  And to see that reservation was successful

  Background:
    Given I am on "Pay Pal Log In Purchase" page

  @API-TEST @API-WDCEE-TEST
  Scenario: purchase
    Given I login to Paypal
    And I am redirected to "Pay Pal Review" page
    When I click "Pay Now"
    And I am redirected to "Pay Pal Success" page
    And I see text "Payment successfully completed."
    And I see text "Transaction ID"
    Then I click "Transaction Identification" link
    And I am redirected to "Wirecard Transaction Details" page
    And I see in table key "Transaction Type" value "debit"
