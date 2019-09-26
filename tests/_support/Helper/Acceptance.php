<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Helper;

use Page\Base;
use Page\CreditCardCancel;
use Page\CreditCardCreateUiBase;
use Page\CreditCardCreateUINon3DWppV2;
use Page\CreditCardCreateUINon3DWppV2PaymentAction;
use Page\CreditCardCreateUIPaymentAction;
use Page\CreditCardCreateUITokenize;
use Page\CreditCardCreateUIWppV2;
use Page\CreditCardCreateUIWppV2PaymentAction;
use Page\CreditCardPayBasedOnReserve;
use Page\CreditCardReserve;
use Page\CreditCardReserveTokenize;
use Page\CreditCardSuccess;
use Page\CreditCardSuccessNon3D;
use Page\CreditCardWppV2SuccessNon3D;
use Page\PayPalCancel;
use Page\PayPalLogIn;
use Page\PayPalLoginPurchase;
use Page\PayPalPayBasedOnReserve;
use Page\PayPalReview;
use Page\PayPalSuccess;
use Page\SimulatorPage;
use Page\Verified;
use Page\WirecardTransactionDetails;


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

    /**
     * @param $gherkinDescription
     * @return string|null
     */
    public static function getPageClassNameByGherkinDescription($gherkinDescription)
    {
        $pageMap = [
            //Generics
            'Verified Page' => Verified::class,
            'SimulatorPage' => SimulatorPage::class,
            'Wirecard Transaction Details' => WirecardTransactionDetails::class,
        ];

        $pageMap = Acceptance::getPaymentSpecificPages($pageMap);

        if (!array_key_exists($pageMap, $gherkinDescription)) {
            return null;
        }

        if (!is_string($pageMap[$gherkinDescription])) {
            return null;
        }

        return $pageMap[$gherkinDescription];
    }

    /**
     * @param array $pageMap
     * @return array
     */
    protected static function getPaymentSpecificPages($pageMap = [])
    {
        $creditCard = [
            'Create Credit Card UI WPPv2 Page' => CreditCardCreateUIWppV2::class,
            'Create Credit Card UI WPPv2 Payment Action Page' => CreditCardCreateUIWppV2PaymentAction::class,
            'Create Credit Card UI non 3D WPPv2 Page' => CreditCardCreateUINon3DWppV2::class,
            'Credit Card Success non 3D WPPv2 Page' => CreditCardWppV2SuccessNon3D::class,
            'Create Credit Card UI non 3D WPPv2 Payment Action Page' => CreditCardCreateUINon3DWppV2PaymentAction::class,
            'Create Credit Card UI Tokenize Page' => CreditCardCreateUITokenize::class,
            'Credit Card Reserve Page' => CreditCardReserve::class,
            'Credit Card Tokenize Reserve Page' => CreditCardReserveTokenize::class,
            'Credit Card Success Page' => CreditCardSuccess::class,
            'Credit Card Cancel Page' => CreditCardCancel::class,
            'Credit Card Success Page Non 3D Page' => CreditCardSuccessNon3D::class,
            'Create Credit Card Pay Based On Reserve' => CreditCardPayBasedOnReserve::class,
            'Create Credit Card UI Payment Action Page' => CreditCardCreateUIPaymentAction::class,
        ];
        $payPal = [
            'Pay Pal Log In' => PayPalLogin::class,
            'Pay Pal Review' => PayPalReview::class,
            'Pay Pal Pay Based On Reserve' => PayPalPayBasedOnReserve::class,
            'Pay Pal Success' => PayPalSuccess::class,
            'Pay Pal Cancel' => PayPalCancel::class,
            'Pay Pal Log In Purchase' => PayPalLoginPurchase::class
        ];
        $paylib = [
            'Paylib Pay Page' => '',
            'Paylib Success Page' => ''
        ];

        return array_merge($creditCard, $payPal, $paylib, $pageMap);
    }
}
