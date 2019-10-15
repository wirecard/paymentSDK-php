<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Purchase for credit card via token

// Enter token-id from successful authorization/purchase for recur payment with credit card using default maid.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/config.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create a amount object as amount which has to be paid by the consumer.
$amount = new Amount(12.59, 'EUR');

// Token ID ist necessary for recur purchase with credit card via token.
$tokenId = array_key_exists('tokenId', $_POST) ? $_POST['tokenId'] : null;

// To make this example usable, even no token ID is provided, a predefined existing token ID is set.
if ($tokenId === null) {
    $tokenId = '4304509873471003';
}

// The redirect URL determines where the consumer should be redirected to
// after an approval/cancellation on the issuer's ACS page.
$redirectUrl = getUrl('return.php?status=success');


// ### Transaction

// The credit card transaction contains all relevant data for the payment process.
$transaction = new CreditCardTransaction();
$transaction->setAmount($amount);
$transaction->setTokenId($tokenId);

// #### Account holder with address
$address = new \Wirecard\PaymentSdk\Entity\Address('DE', 'Berlin', 'Teststrasse');
$address->setStreet2('Test street 2');
$address->setStreet3('Test street 3');
$address->setPostalCode('13353');

$accountHolder = new \Wirecard\PaymentSdk\Entity\AccountHolder();
$accountHolder->setEmail('john.doe@test.com');
$accountHolder->setPhone('03018425165');
$accountHolder->setWorkPhone('0765573242');
$accountHolder->setMobilePhone('041232342');
$accountHolder->setDateOfBirth(new \DateTime('1973-12-07'));
$accountHolder->setAddress($address);

// 3DS parameters
// ### Contains information for the 3DS Requestor
// Information about how the 3DS Requestor authenticated the cardholder before or during the transaction
// Possible values 01 guest login, 02 User Account in Shop, 03 federated id, 04 issuer of card credentials, 05 third-party authentication, 06 FIDO authentication
$authenticationInfo = new \Wirecard\PaymentSdk\Entity\AccountInfo();
$authenticationInfo->setAuthMethod(\Wirecard\PaymentSdk\Constant\AuthMethod::GUEST_CHECKOUT);
$authenticationInfo->setAuthTimestamp();
// Indicates if a challenge is requested for this transaction, 01 no preference, 02 no challenge, 03 challenge requested 3DS, 04 challenge requested Mandate
$authenticationInfo->setChallengeInd(\Wirecard\PaymentSdk\Constant\ChallengeInd::NO_PREFERENCE);
// ### Contains additional information about the Cardholder's account provided by the 3DS Requestor
// Account creation date
$authenticationInfo->setCreationDate(new DateTime());
// Account update date
$authenticationInfo->setUpdateDate(new DateTime());
// Account password change date
$authenticationInfo->setPassChangeDate(new DateTime());
// Account first usage of the address
$authenticationInfo->setShippingAddressFirstUse(new DateTime());
// Card creation date
$authenticationInfo->setCardCreationDate(new DateTime());
// Number of transactions (successful and abandoned) for this cardholder account across all payment accounts in the previous 24 hours
$authenticationInfo->setAmountTransactionsLastDay(2);
// Number of transactions (successful and abandoned) for this cardholder account across all payment accounts in the previous year
$authenticationInfo->setAmountTransactionsLastYear(500);
// Number of card attempts in the previous 24 hours
$authenticationInfo->setAmountCardTransactionsLastDay(1);
// Number of purchases with this cardholder account during the previous six months
$authenticationInfo->setAmountPurchasesLastSixMonths(30);
// Set accountInfo for AccountHolder
$accountHolder->setAccountInfo($authenticationInfo);
// Additional information about the account provided by the 3DS requestor. Limited to 64 characters
$accountHolder->setCrmId('12daw2r');
$transaction->setAccountHolder($accountHolder);

// ### Merchant's assessment of the level of fraud risk for the specific authentication for both the cardholder and the authentication being conducted
$merchantRiskIndicator = new \Wirecard\PaymentSdk\Entity\RiskInfo();
// Indicates whether cardholder is placing an order for merchandise with a future availability or release date. Merchandise available '01', Future availability '02'
$merchantRiskIndicator->setAvailability(\Wirecard\PaymentSdk\Constant\RiskInfoAvailability::MERCHANDISE_AVAILABLE);
// For electronic delivery, the email address the merchandise was delivered
$merchantRiskIndicator->setDeliveryEmailAddress('max.muster@mail.com');
// Indicates the merchandise delivery timeframe. Electronic Delivery 01, Same day shipping 02, Overnight shipping 03, Two-day or more shipping 04
$merchantRiskIndicator->setDeliveryTimeFrame(\Wirecard\PaymentSdk\Constant\RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY);
// Expected delivery date for pre-ordered goods
$merchantRiskIndicator->setPreOrderDate(new DateTime());
// Was the good already bought before. First time ordered 01, Reordered 02
$merchantRiskIndicator->setReorderItems(\Wirecard\PaymentSdk\Constant\RiskInfoReorder::FIRST_TIME_ORDERED);
$transaction->setRiskInfo($merchantRiskIndicator);
// Transaction type, classification of goods derived from ISO 8583. Goods/Service purchase 01, Check Acceptance 03, Account Funding 10, Quasi-Cash Transaction 11, Prepaid activation and Loan 28
$transaction->setIsoTransactionType(\Wirecard\PaymentSdk\Constant\IsoTransactionType::CHECK_ACCEPTANCE);
// ### Contains browser information. This field is required when deviceChannel is set to 02.
$browser = new \Wirecard\PaymentSdk\Entity\Browser();
// Defines the challenge window size through the given width in px
$browser->setChallengeWindowSize(500);
$transaction->setBrowser($browser);

// ### Transaction Service

// The service is used to execute the payment operation itself.
// A response object is returned.
// For this example the default maid gets used for recur payment. For integration the configuration must be set to
// the corresponding maid. (3D-maid or Non-3D-maid)
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse):
    echo 'Payment successfully completed.<br>';
    echo getTransactionLink($baseUrl, $response);
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <button type="submit" class="btn btn-primary">Cancel the payment</button>
    </form>

    <?php
// In case of a failed transaction, a `FailureResponse` object is returned.
elseif ($response instanceof FailureResponse):
    // In our example we iterate over all errors and display them in a raw state.
    // You should handle them based on the given severity as error, warning or information.
    foreach ($response->getStatusCollection() as $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Entity\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occurred.<br>', $severity, $code, $description);
    }
endif;

require __DIR__ . '/../../inc/footer.php';
