Feature: check_credit_card_wppv2_non_3DS_functionality_happy_path
  As a user
  I want to make a purchase with a Credit Card 3DS
  And to see that transaction was successful
  And to be able to cancel the transaction

  Background:
    Given I am on "Create Credit Card UI non 3D WPPv2 Page" page
    When I fill fields with "Valid Credit Card Data"
    And I click "Save"

  @default_gateway
  Scenario: try purchase_check
    Given I am redirected to "Credit Card Reserve WPPv2 Page" page
    Then I see text "Reservation successfully completed."
    And I see text "Transaction ID"

  @default_gateway
  Scenario: try void_purchase_check
    Given I am redirected to "Credit Card Reserve WPPv2 Page" page
    And I click "Cancel the payment"
    And I am redirected to "Credit Card Cancel Page" page
    And I see text "Payment successfully cancelled."
    And I see text "Transaction ID"