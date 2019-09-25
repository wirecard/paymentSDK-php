Feature: PaylibPurchaseHappyPath
  As a user
  I want to make a purchase with a Paylib
  And to see that purchase was successful

#psp-wirecard@wl.net
  #password
  @API-TEST
  Scenario:
    Given I am on "Paylib Pay" page
    When I click on "Redirect to Paylib"
    Then I am redirected to "Paylib authentication" page
    And I enter "Paylib mail" in field "login"
    And I click "Verify"
    Then I enter "Paylib password" in field "password"
    And I click "Verify"
    Then I am redirected to "Paylib Success" page
    And I see text "Payment successfully completed."
    And I see text "Transaction ID"
