<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCreateUiWppV2Base extends CreditCardCreateUiBase
{
    //page type
    const TYPE = 'WPP';

    //include url of current page
    public $URL = '/CreditCard/createUiWppV2';

    //wirecard seamless frame name
    public $wirecardFrame = 'wirecard-integrated-payment-page-frame';

    //page elements
    public $elements = array(
        'First name' => "//*[@id='pp-cc-first-name']",
        'Last name' => "//*[@id='pp-cc-last-name']",
        'Card number' => "//*[@id='pp-cc-account-number']",
        'CVV' => "//*[@id='pp-cc-cvv']",
        'Valid until month / year' => "//*[@id='pp-cc-expiration-date']",
        'Save' => "//*[@class='btn btn-primary']",
        'Credit Card payment form' => "//*[@id='payment-form']"
    );

    /**
     * Method switchFrame
     * @param boolean $wpp2
     */
    public function switchFrame($wpp2 = true)
    {
        parent::switchFrame($wpp2);
    }

    /**
     * Method Method fillCreditCardFields
     * @param string $cardData
     * @param null $type
     */
    public function fillCreditCardFields($cardData, $type = null)
    {
        parent::fillCreditCardFields($cardData, self::TYPE);
    }
}
