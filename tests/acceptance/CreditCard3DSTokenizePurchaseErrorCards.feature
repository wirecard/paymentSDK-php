Feature: CreditCard3DSTokenizePurchaseErrorCards
  As a user
  I want to check that I'm not able to make transactions with error cards

  Background:
    Given I am on "Create Credit Card UI Tokenize Page" page

  # @API-TEST @API-WDCEE-TEST @NOVA
  Scenario Outline: purchaseErrorNoRedirect3DS
    Given I fill fields with <credit_card_data>
    And I enter "500" in field "Amount"
    And I choose "EUR" in field "Currency"
    When I click "Save"
    Then I am redirected to "Credit Card Tokenize Reserve Page" page
    And I see text <error_message>

    Examples:
      | credit_card_data                                   | error_message |
      | "Card Not Enrolled VISA Data"                      | "500.1072" |
      | "Card Not Enrolled Mastercard Data"                | "500.1072" |
      | "MPI Error VISA Data"                              | "500.1074" |
      | "MPI Error Mastercard Data"                        | "500.1074" |

  # @API-TEST @API-WDCEE-TEST
  Scenario Outline: purchaseErrorNoRedirect3DS
    Given I fill fields with <credit_card_data>
    And I enter "500" in field "Amount"
    And I choose "EUR" in field "Currency"
    When I click "Save"
    Then I am redirected to "Credit Card Tokenize Reserve Page" page
    And I see text <error_message>

    Examples:
      | credit_card_data                                   | error_message |
      | "Card Not Enrolled Maestro Data"                   | "500.1072" |
      | "Unable to Verify Enrolment Maestro Data"          | "500.1073" |
      | "Unable to Verify Enrolment VISA Data"             | "500.1073" |
      | "Unable to Verify Enrolment Mastercard Data"       | "500.1073" |
      | "MPI Error Maestro Data"                           | "500.1074" |

  # @API-TEST @API-WDCEE-TEST
  Scenario Outline: purchaseErrorRedirect3DS
    Given I fill fields with <credit_card_data>
    And I enter "500" in field "Amount"
    And I choose "EUR" in field "Currency"
    When I click "Save"
    And I am redirected to "Credit Card Tokenize Reserve Page" page
    And I click "Redirect to 3-D Secure page"
    And I am redirected to "Verified Page" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    Then I am redirected to "Credit Card Success Page" page
    And I see text <error_message>

    Examples:
      | credit_card_data                                    | error_message |
      | "Consumer failed or Cancelled auth VISA Data"       | "500.1076" |
      | "Consumer failed or Cancelled auth Mastercard Data" | "500.1076" |
      | "Consumer failed or Cancelled auth Maestro Data"    | "500.1076" |
      | "Auth not completed tech problem VISA Data"         | "500.1077" |
      | "Auth not completed tech problem Mastercard Data"   | "500.1077" |
      | "Auth not completed tech problem Maestro Data"      | "500.1077" |