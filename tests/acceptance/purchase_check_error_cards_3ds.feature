Feature: check_credit_card_3DS_functionality_error_cards
  As a user
  I want to check that I'm not able to make transactions with error cards

  @default_gateway
  Scenario Outline: purchase_error_case
    Given I am on "Create Credit Card UI Page" page
    When I fill fields with <credit_card_data>
    And I enter "70" in field "Amount"
    And I click "Save"
    Then I am redirected to "Credit Card Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    Then I am redirected to "Verified Page" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    Given I am redirected to "Credit Card Success Page" page
    Then I see text <error_message>

    Examples:
      | credit_card_data                                | error_message |
      | "Auth Failed VISA Data"                         | "500.1076" |
      | "Auth Failed Mastercard Data"                   | "500.1076" |
      | "Auth Failed Maestro Data"                      | "500.1076" |
      | "Unable Auth VISA Data"                         | "201.0000" |
      | "Unable Auth Mastercard Data"                   | "201.0000" |
      | "Unable Auth Maestro Data"                      | "201.0000" |
      | "Auth Failed Sys Err VISA Data"                 | "201.0000" |
      | "Auth Failed Sys Err Mastercard Data"           | "201.0000" |
      | "Auth Failed Sys Err Maestro Data"              | "201.0000" |
      | "Card Holder Not Participating VISA Data"       | "500.1072" |
      | "Card Holder Not Participating Mastercard Data" | "500.1072" |
      | "Card Holder Not Participating Maestro Data"    | "500.1072" |
      | "Unable Verify Enrollment VISA Data"            | "500.1073" |
      | "Unable Verify Enrollment Mastercard Data"      | "500.1073" |
      | "Unable Verify Enrollment Maestro Data"         | "500.1073" |
      | "Sys Err Prevent Enrollment VISA Data"          | "500.1074" |
      | "Sys Err Prevent Enrollment Mastercard Data"    | "500.1074" |
      | "Sys Err Prevent Enrollment Maestro Data"       | "500.1074" |