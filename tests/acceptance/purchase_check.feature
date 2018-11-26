Feature: check_credit_card_3DS_functionality
  As a user
  I want to make a purchase with a Credit Card 3DS
  And to see that transaction was successful
  And to be able to cancel the transaction

  @default_gateway
  Scenario:
    Given I am on "Create Credit Card UI Page" page
    When I fill fields with "Valid Credit Card Data"
    And I click "Save"
    Then I am redirected to "Credit Card Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    Then I am redirected to "Verified by Visa Page" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"

  @sg_secure_gateway @sg_gateway
  Scenario:
    Given I am on "Create Credit Card UI Page" page
    When I fill fields with "Valid Credit Card Data"
    And I click "Save"
    Then I am redirected to "Credit Card Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    Then I am redirected to "SimulatorPage" page
    And I click "Submit"

  @default_gateway @sg_secure_gateway @sg_gateway
  Scenario: try purchase_check
    Given I am redirected to "Credit Card Success Page" page
    Then I see text "Payment successfully completed."
    And I see text "Transaction ID"

  @default_gateway @sg_secure_gateway @sg_gateway
  Scenario: try refund_check
    Given I am redirected to "Credit Card Success Page" page
    And I see text "Transaction ID"
    And I note the "Transaction Identification"
    When I am on "Credit Card Cancel Page" page
    And I enter "Noted Transaction Identification" in field "Transaction ID to be refunded"
    And I click "Refund"
    Then I see text "Payment successfully cancelled."
    And I see text "Transaction ID"
