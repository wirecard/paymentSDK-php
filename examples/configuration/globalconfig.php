<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

// # Configuration with httpUser 16390-testing

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.
// Included payment methods: Guaranteed Invoice, Installment, Direct Debit, Payment on Invoice / Payment in Advance,
// Alipay Cross-border, Przelewy24, giropay, eps, iDEAL, Sofort., Payolution

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Transaction\EpsTransaction;
use Wirecard\PaymentSdk\Transaction\GiropayTransaction;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayDirectDebitTransaction;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\Transaction\AlipayCrossborderTransaction;
use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\Transaction\PtwentyfourTransaction;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\PayolutionBtwobTransaction;

// ## Connection

// The basic configuration requires the base URL (Server Address) for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://api-test.wirecard.com';
$httpUser = '16390-testing';
$httpPass = '3!3013=D3fD8X7';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.
// For new Config you need the merchant account id (MAID) and secret key to add the payment specific configuration.

// ### Guaranteed Invoice by Wirecard / Ratepay

$ratepayInvoiceMAID = '7d7edecb-b008-4f05-9103-308c81cf2ea2';
$ratepayInvoiceKey = '555d998b-15db-46a9-8f1f-d9bc3ec66b19';
$ratepayInvoiceConfig = new PaymentMethodConfig(RatepayInvoiceTransaction::NAME, $ratepayInvoiceMAID, $ratepayInvoiceKey);
$config->add($ratepayInvoiceConfig);

// ### Guaranteed Installment by Wirecard / Ratepay

$ratepayInstallmentMAID = '5fcfd1ba-10bd-4ad4-b8e7-9a1926f5b3fe';
$ratepayInstallmentKey = 'c92236cc-9fea-4d45-93d9-707fc7964da0';
$ratepayInstallmentConfig = new PaymentMethodConfig(RatepayInstallmentTransaction::NAME, $ratepayInstallmentMAID, $ratepayInstallmentKey);
$config->add($ratepayInstallmentConfig);


// ### Guaranteed Direct Debit by Ratepay

$ratepayDDMAID = '3cfb0fb7-59e2-4d92-847f-37121d633844';
$ratepayDDKey = 'cf0b29fc-a6ab-474d-b6be-92e9596e9107';
$ratepayDirectDebit = new PaymentMethodConfig(RatepayDirectDebitTransaction::NAME, $ratepayDDMAID, $ratepayDDKey);
$config->add($ratepayDirectDebit);

// ### Payment on Invoice / Payment in Advance

$poipiaMAID = 'dcd72c94-25df-4794-8197-daf029c82d65';
$poipiaSecret = 'cbdf53d2-b9ff-4355-80d5-8836342ac336';
$poipiaConfig = new PaymentMethodConfig(PoiPiaTransaction::NAME, $poipiaMAID, $poipiaSecret);
$config->add($poipiaConfig);

// ### Alipay Cross-border

$alipaycrossborderMAID = '47cd4edf-b13c-4298-9344-53119ab8b9df';
$alipaycrossborderSecretKey = '94fe4f40-16c5-4019-9c6c-bc33ec858b1d';
$alipaycrossborderConfig = new PaymentMethodConfig(AlipayCrossborderTransaction::NAME, $alipaycrossborderMAID, $alipaycrossborderSecretKey);
$config->add($alipaycrossborderConfig);

// ### Przelewy24

$p24Maid = '86451785-3ed0-4aa1-99b2-cc32cf54ce9a';
$p24Secret = 'fdd54ea1-cef1-449a-945c-55abc631cfdc';
$p24Config = new PaymentMethodConfig(PtwentyfourTransaction::NAME, $p24Maid, $p24Secret);
$config->add($p24Config);

// ### giropay
$giropayMAID = '9b4b0e5f-1bc8-422e-be42-d0bad2eadabc';
$giropaySecret = '0c8c6f3a-1534-4fa1-99d9-d1c644d43709';
$giropayConfig = new PaymentMethodConfig(GiropayTransaction::NAME, $giropayMAID, $giropaySecret);
$config->add($giropayConfig);

// ### eps
$epsMAID = '1f629760-1a66-4f83-a6b4-6a35620b4a6d';
$epsSecret = '20c6a95c-e39b-4e6a-971f-52cfb347d359';
$epsConfig = new PaymentMethodConfig(EpsTransaction::NAME, $epsMAID, $epsSecret);
$config->add($epsConfig);

// ### iDEAL

$IdealMAID = '4aeccf39-0d47-47f6-a399-c05c1f2fc819';
$IdealSecretKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$IdealConfig = new PaymentMethodConfig(IdealTransaction::NAME, $IdealMAID, $IdealSecretKey);
$config->add($IdealConfig);

// ### Sofortbanking

$sofortMAID = '6c0e7efd-ee58-40f7-9bbd-5e7337a052cd';
$sofortSecretKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$sofortConfig = new PaymentMethodConfig(SofortTransaction::NAME, $sofortMAID, $sofortSecretKey);
$config->add($sofortConfig);

// ### Payolution Invoice

$payolutionInvoiceMAID = '2048677d-57f4-44b0-8d67-9014c6631d5f';
$payolutionInvoiceSecretKey = '74bd2f0c-6d1b-4e9a-b278-abc34b83ab9f';
$payolutionInvoiceConfig = new PaymentMethodConfig(PayolutionInvoiceTransaction::NAME, $payolutionInvoiceMAID, $payolutionInvoiceSecretKey);
$config->add($payolutionInvoiceConfig);

// ### Payolution B2B
$payolutionB2BMAID = '2048677d-57f4-44b0-8d67-9014c6631d5f';
$payolutionB2BSecretKey = '74bd2f0c-6d1b-4e9a-b278-abc34b83ab9f';
$payolutionB2BConfig = new PaymentMethodConfig(PayolutionBtwobTransaction::NAME, $payolutionB2BMAID, $payolutionB2BSecretKey);
$config->add($payolutionB2BConfig);

// ### SEPA Direct Debit
$sepaDirectDebitMAID = '933ad170-88f0-4c3d-a862-cff315ecfbc0';
$sepaDirectDebitKey = 'ecdf5990-0372-47cd-a55d-037dccfe9d25';
// SEPA requires the creditor ID, therefore a different config object is used.
$sepaDirectDebitConfig = new SepaConfig(SepaDirectDebitTransaction::NAME, $sepaDirectDebitMAID, $sepaDirectDebitKey);
$sepaDirectDebitConfig->setCreditorId('DE98ZZZ09999999999');
$config->add($sepaDirectDebitConfig);

// ### SEPA Credit Transfer
$sepaCreditTransferMAID = '59a01668-693b-49f0-8a1f-f3c1ba025d45';
$sepaCreditTransferKey = 'ecdf5990-0372-47cd-a55d-037dccfe9d25';
// SEPA requires the creditor ID, therefore a different config object is used.
$sepaCreditTransferConfig = new SepaConfig(SepaCreditTransferTransaction::NAME, $sepaCreditTransferMAID, $sepaCreditTransferKey);
$config->add($sepaCreditTransferConfig);