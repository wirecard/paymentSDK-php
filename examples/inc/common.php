<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\Entity\CustomField;

// # Custom functions
// For requests which include an URL for e.g. notifications, it is easier to get the URL from the server variables.

/**
 * Get the URL from the server variables.
 * @param string $path relative path
 * @return string
 */
function getUrl($path)
{
    $protocol = 'http';

    if ($_SERVER['SERVER_PORT'] === 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on')) {
        $protocol .= 's';
    }

    $host = $_SERVER['HTTP_HOST'];
    $request = $_SERVER['PHP_SELF'];
    return dirname(sprintf('%s://%s%s', $protocol, $host, $request)) . '/' . $path;
}

/**
 * Creates a html link with transaction url
 * @param string $baseUrl
 * @param Wirecard\PaymentSdk\Response\SuccessResponse $response
 * @param Wirecard\PaymentSdk\Config\Config|null $config
 * @return string
 */
function getTransactionLink($baseUrl, $response, $config = null)
{
    if ($config !== null) {
        $authorization = $config->getHttpUser() . ':' . $config->getHttpPassword();
        $baseUrl = str_replace("//", "//$authorization@", $baseUrl);
    }

    $transactionId = $response->getTransactionId();
    $output = 'Transaction ID: ';
    $output .= sprintf(
        '<a href="' . $baseUrl . '/engine/rest/merchants/%s/payments/%s">',
        $response->findElement('merchant-account-id'),
        $transactionId
    );
    $output .= $transactionId;
    $output .= '</a>';
    return $output;
}

/**
 * Creates a custom field with key and value
 * @param string $key
 * @param string $value
 * @param string $prefix
 * @return CustomField
 */
function prepareCustomField($key, $value, $prefix = '')
{
    $customField = new CustomField($key, $value);
    $customField->setPrefix($prefix);
    return $customField;
}
