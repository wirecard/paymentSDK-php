<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # EPS payment

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\BankAccount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\EpsTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Set amount
$amount = new Amount(18.4, 'EUR');

// ### Set success, cancel and failure url
$redirect = new Redirect(
    getUrl('return.php?status=success'),
    getUrl('return.php?status=cancel'),
    getUrl('return.php?status=failure')
);
// ### Set Bank Account
$bankAccount = new BankAccount();
$bankAccount->setBic("BWFBATW1XXX");
$bankAccount->setIban("NL13TEST0123456789");

$transaction = new EpsTransaction();

// ### Mandatory fields
$transaction->setAmount($amount);
$transaction->setRedirect($redirect);
$transaction->setBankAccount($bankAccount);

// ### Optional fields
// For the full list of fields see: https://doc.wirecard.com/RestApi_Fields.html
$transaction->setDescriptor('eps pay 123');

// ### Transaction Service
// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);

// ### Response handling

// The response from the service can be used for disambiguation.
// Since a redirect for successful transactions is defined, a FormInteractionResponse is returned
// if the transaction was successful.
if ($response instanceof InteractionResponse) {
    die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");
    
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    echo "The transaction has failed.<br>";

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
}

require __DIR__ . '/../../inc/footer.php';
