<?php
/**
 * Shop System Plugins:
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
        $link_parts["user"] = $username;
        $link_parts["pass"] = $password;

        $new_link = $link_parts['scheme'] . '://' . $link_parts["user"] . ":" . $link_parts["pass"] . "@" . $link_parts['host'] . $link_parts['path'];
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

    public static function getCardDataFromDataFile($cardDataType) {
        $gateway_env = getenv('GATEWAY');

        if (! $gateway_env) {
            $gateway = 'sg_secure_gateway';
        }
//        if ($gateway_env === 'NOVA' || $gateway_env === 'API-TEST' || $gateway_env === 'API-WDCEE-TEST' ) {
//            $gateway = 'default_gateway';
//
//        } else if ($gateway_env === 'SECURE-TEST-SG') {
//            $gateway = 'sg_secure_gateway';
//
//        } else if ($gateway_env === 'TEST-SG') {
//            $gateway = 'sg_gateway';
//        }

        $filedata = file_get_contents('tests/_support/data.json');
        $data = json_decode($filedata); // decode the JSON feed
        return $data->$gateway->$cardDataType;
    }
}
