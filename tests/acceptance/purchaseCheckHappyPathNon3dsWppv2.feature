Feature: checkCreditCardWppv2Non3DSFunctionalityHappyPath
  As a user
  I want to make a purchase with a Credit Card 3DS
  And to see that transaction was successful
  And to be able to cancel the transaction

  Background:
    Given I am on "Create Credit Card UI non 3D WPPv2 Page" page
    When I fill fields with "Valid Credit Card Data"
    And I click "Save"

#  @API-TEST @API-WDCEE-TEST
  Scenario: try purchaseCheck
    Given I am redirected to "Credit Card Reserve WPPv2 Page" page
    Then I see text "Reservation successfully completed."
    And I see text "Transaction ID"

#  @API-TEST @API-WDCEE-TEST
  Scenario: try voidPurchaseCheck
    Given I am redirected to "Credit Card Reserve WPPv2 Page" page
    And I click "Cancel the payment"
    And I am redirected to "Credit Card Cancel Page" page
    And I see text "Payment successfully cancelled."
    And I see text "Transaction ID"