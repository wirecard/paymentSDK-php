<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Helper;

class Acceptance extends \Codeception\Module
{
    /**
     * Method returns modified link
     *
     * @param string $link
     * @param string $username
     * @param string $password
     * @return string
     */
    public static function formAuthLink($link, $username, $password)
    {
        $link_parts = parse_url($link);
        $new_link = sprintf(
            '%s://%s:%s@%s%s',
            $link_parts['scheme'],
            $username,
            $password,
            $link_parts['host'],
            $link_parts['path']
        );
        return $new_link;
    }

    /**
     * Method returns last part of the link
     *
     * @param string $link
     * @return string
     */
    public static function getTransactionIDFromLink($link)
    {
        $transaction_id = explode('/', $link);
        return $transaction_id = end($transaction_id);
    }

    /**
     * Method returns gateway type based on environment variable
     *
     * @return string
     */
    public static function getGateway()
    {
        $gatewayEnv = getenv('GATEWAY');
        $gateway = 'default_gateway';
        if ('SECURE-TEST-SG' == $gatewayEnv) {
            $gateway = 'sg_secure_gateway';
        } elseif ('TEST-SG' == $gatewayEnv) {
            $gateway = 'sg_gateway';
        }
        return $gateway;
    }

    /**
     * Method returns card data from file
     *
     * @param string $cardDataType
     * @return string
     */
    public static function getCardDataFromDataFile($cardDataType)
    {
        $gateway = self::getGateway();
        $fileData = file_get_contents('tests/_data/data.json');
        $data = json_decode($fileData); // decode the JSON feed
        return $data->$gateway->$cardDataType;
    }

    /**
     * Method getDataFromDataFile
     * @param string $fileName
     * @return string
     *
     * @since 3.7.2
     */
    public static function getDataFromDataFile($fileName)
    {
        // decode the JSON feed
        $json_data = json_decode(file_get_contents($fileName));
        if (!$json_data) {
            $error = error_get_last();
            echo 'Failed to get customer data from tests/_data/...json. Error was: ' . $error['message'];
            return;
        }
        return $json_data;
    }
}
