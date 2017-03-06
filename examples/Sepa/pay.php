<?php

// # SEPA amount payment
// The method `pay` of the _transactionService_ provides the means
// to execute a payment with an amount (also known as debit).

// To include the necessary files, use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Entity\Money;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\TransactionService;

// Create a money object as amount which has to be payed by the consumer.
$amount = new Money(12.59, 'EUR');

// ### Config

// Since payment method may have a different merchant ID, a config collection is created.
$configCollection = new Config\PaymentMethodConfigCollection();

// Create and add a configuration object with the settings for SEPA.
$sepaMId = '4c901196-eff7-411e-82a3-5ef6b6860d64';
$sepaKey = 'ecdf5990-0372-47cd-a55d-037dccfe9d25';
$sepaDdConfig = new Config\PaymentMethodConfig(SepaTransaction::DIRECT_DEBIT, $sepaMId, $sepaKey);

// In order to execute a pay transaction you also have to provide your creditor ID.
// Please add it to the config as a specific property with the key 'creditor-id'.
$sepaDdConfig->addSpecificProperty('creditor-id', 'DE98ZZZ09999999999');
$configCollection->add($sepaDdConfig);

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, $configCollection, 'EUR');


// ## Transaction

// Create a `SepaTransaction` object, which contains all relevant data for the payment process.
$transaction = new SepaTransaction();
$transaction->setAmount($amount);
$transaction->setIban($_POST['iban']);

if (null !== $_POST['bic']) {
    $transaction->setBic($_POST['bic']);
}

// The account holder (first name, last name) is required.
$accountHolder = new AccountHolder('Doe');
$accountHolder->setFirstName('Jane');
$transaction->setAccountHolder($accountHolder);

// A mandate with ID and signed date is required.
$mandate = new Mandate('12345678');
$transaction->setMandate($mandate);

// The service is used to execute the pay (pending-debit) operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);

// ## Response handling

// The response from the service can be used for disambiguation.
// In case of a successful transaction, a `SuccessResponse` object is returned.
if ($response instanceof SuccessResponse) {
    echo sprintf('Payment with id %s successfully completed.<br>', $response->getTransactionId());
    ?>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="submit" value="cancel the payment">
    </form>
    <?php
// In case of a failed transaction, a `FailureResponse` object is returned.
} elseif ($response instanceof FailureResponse) {
    // In our example we iterate over all errors and display them in a raw state.
    // You should handle them based on the given severity as error, warning or information.
    foreach ($response->getStatusCollection() as $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Entity\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occured.<br>', $severity, $code, $description);
    }
}
