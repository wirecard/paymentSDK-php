<?php
/* Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

use Facebook\WebDriver\Exception\TimeOutException;

class PayPalLoginPurchase extends PayPalLogIn
{
    //include url of current page
    public $URL = 'PayPal/pay.php';

    /**
     * Method performPaypalLogin
     *
     * @since 3.7.2
     */
    public function performPaypalLogin()
    {
        parent::performPaypalLogin();
    }
}
