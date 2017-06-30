<?php
// # Bancontact / Mister Cash return after transaction

// The consumer gets redirected to this page after a Bancontact / Mister Cash transaction.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\BancontactMrCashTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Validation

// Set a public key for certificate pinning used for response signature validation, this certificate needs to be always
// up to date
$config->setPublicKey(file_get_contents(__DIR__ . '/../inc/api-test.wirecard.com.crt'));


// ## Transaction

// ### Transaction Service

// The `TransactionService` is used to determine the response from the service provider.
$service = new TransactionService($config);
$response = $service->handleResponse($_POST);


// ## Payment results

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    $xmlResponse = new SimpleXMLElement($response->getRawData());
    $transactionType = $response->getTransactionType();
    if ($transactionType === 'authorization') {
        echo 'Reservation';
    } else {
        echo 'Payment';
    }
    echo ' successfully completed.<br>';
    echo sprintf('Response validation status: %s <br>', $response->isValidSignature() ? 'true' : 'false');
    echo getTransactionLink($baseUrl, $response);
    ?>
    <br>
    <?php
    if ($response->getTransactionType() === BancontactMrCashTransaction::TYPE_AUTHORIZATION) {
        ?>
        <form action="cancel.php" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <input type="submit" value="Cancel">
        </form>
        <form action="pay-based-on-reserve.php" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <input type="submit" value="Capture the payment">
        </form>
        <?php
    }
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
