<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Sofortbanking return after transaction

// The consumer gets redirected to this page after a Sofortbanking transaction.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/globalconfig.php';

require __DIR__ . '/../../inc/header.php';
require __DIR__ . '/../../inc/payload/sofort.php';

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

// ### Validation

// Set a public key for certificate pinning used for response signature validation, this certificate needs to be always
// up to date
$config->setPublicKey(file_get_contents(__DIR__ . '/../../inc/api-test.wirecard.com.crt'));


// ### Transaction

// ### Transaction Service

// The `TransactionService` is used to determine the response from the service provider.
$service = new TransactionService($config);
// If there is response data from the service provider handle response
if ($_POST) {
    $response = $service->handleResponse($_POST);


// ## Payment results

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
    if ($response instanceof SuccessResponse) {
        echo 'Payment successfully completed.<br>';
        echo sprintf('Response validation status: %s <br>', $response->isValidSignature() ? 'true' : 'false');
        echo getTransactionLink($baseUrl, $response);
        ?>
        <br>
        <br>
        <form action="../SepaDirectDebit/pay.php" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <button type="submit" class="btn btn-primary">Request a new payment based on this payment</button>
        </form>
        <br>
        <form action="pay-based-on-pay.html" method="post">
            <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
            <button type="submit" class="btn btn-primary">Request a credit based on this payment</button>
        </form>
        <?php
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
