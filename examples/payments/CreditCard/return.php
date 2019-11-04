<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Handling the response of a transaction

// When a transaction is finished, the response from Wirecard can be read and processed.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/config.php';
require __DIR__ . '/../../inc/header.php';
require __DIR__ . '/../../inc/payload/creditcard.php';

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;

// Set a public key for certificate pinning used for response signature validation, this certificate needs to be always
// up to date
$config->setPublicKey(file_get_contents(__DIR__ . '/../../inc/api-test.wirecard.com.crt'));

// ### Transaction

// ### Transaction Service
// The `TransactionService` is used to determine the response from the service provider.
$transactionService = new TransactionService($config);

// The 3D-Secure page redirects to the _returnUrl_, which points to this file. To continue the payment process
// the sent data can be fed directly to the transaction service via the method `handleResponse()`.
// If there is response data from the service provider handle response
if ($_POST) {
    $response = $transactionService->handleResponse($_POST);

// ## Payment results

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        echo 'Payment successfully completed.<br>';
        echo sprintf('Response validation status: %s <br>', $response->isValidSignature() ? 'true' : 'false');
        echo getTransactionLink($baseUrl, $response);
        echo '<br>Credit Card Token-Id: ' . $response->getCardTokenId();
        ?>
        <br>
        <form action="cancel.php" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <input type="hidden" name="amount" value="<?= $response->getRequestedAmount()->getValue() ?>"/>
            <input type="hidden" name="currency" value="<?= $response->getRequestedAmount()->getCurrency() ?>"/>
            <button type="submit" class="btn btn-primary">Cancel the payment</button>
        </form>
        <?php
        // ##  Example of BackendService use
        // For more info on BackendService please see the backendService example under Features
        // Create an transaction with the response transaction id.
        $backendService = new BackendService($config);
        $transaction = new CreditCardTransaction();
        $transaction->setParentTransactionId($response->getTransactionId());
        // ### Retrieve possible operations for the transaction. An array of possible operations is returned
        echo '<br>Possible backend operations: ' .
             print_r($backendService->retrieveBackendOperations($transaction, true), true) . '<br>';
        // ### Check it the state of the transaction is final.
        echo '<br>Is ' . $response->getTransactionType() .
             ' final: ' . printf($backendService->isFinal($response->getTransactionType())) . '<br>';
        // ### Get order state of the transaction
        echo '<br>Order state: ' . $backendService->getOrderState($response->getTransactionType());

// In case of a failed transaction, a `FailureResponse` object is returned.
    } elseif ($response instanceof FailureResponse) {
        echo sprintf('Response validation status: %s <br>', $response->isValidSignature() ? 'true' : 'false');

        // In our example we iterate over all errors and echo them out.
        // You should display them as error, warning or information based on the given severity.
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
// Otherwise a cancel information is printed
} else {
    echo 'The transaction has been cancelled.<br>';
}

require __DIR__ . '/../../inc/footer.php';
