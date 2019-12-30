Feature: CrediCard3DSTokenizePurchaseHappyPath
  As a user
  I want to make a purchase with a Credit Card 3DS
  And to see that transaction was successful
  And to be able to cancel the transaction

  Background:
    Given I am on "Create Credit Card UI Tokenize Page" page
    When I fill fields with "Valid Credit Card Data"
    And I enter "70" in field "Amount"
    And I choose "EUR" in field "Currency"
    And I click "Save"

  # @API-TEST @API-WDCEE-TEST @NOVA
  Scenario: purchase
    Given I am redirected to "Credit Card Tokenize Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    When I am redirected to "Verified Page" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    And I am redirected to "Credit Card Success Page" page
    Then I see text "Payment successfully completed."
    And I see text "Transaction ID"

  # @API-TEST @API-WDCEE-TEST
  Scenario: voidPurchase
    Given I am redirected to "Credit Card Tokenize Reserve Page" page
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

  # @SECURE-TEST-SG
  Scenario: purchase
    Given I am redirected to "Credit Card Tokenize Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    When I am redirected to "SimulatorPage" page
    And I click "Submit"
    And I am redirected to "Credit Card Success Page" page
    Then I see text "Payment successfully completed."
    And I see text "Transaction ID"

  # @SECURE-TEST-SG
  Scenario: voidPurchase
    Given I am redirected to "Credit Card Tokenize Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    And I am redirected to "SimulatorPage" page
    And I click "Submit"
    When I am redirected to "Credit Card Success Page" page
    And I see text "Transaction ID"
    And I note the "Transaction Identification"
    And I am on "Credit Card Cancel Page" page
    And I enter "Noted Transaction Identification" in field "Transaction ID to be refunded"
    And I enter "70" in field "Amount"
    And I choose "EUR" in field "Currency"
    And I click "Refund"
    Then I see text "Payment successfully cancelled."
    And I see text "Transaction ID"