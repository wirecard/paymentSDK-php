<?php
/**
 * Shop System Plugins:
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class CreditCardCreateUINon3DWppV2 extends CreditCardCreateUiBase
{
    const TYPE = 'WPP';

    // include url of current page
    public $URL = '/CreditCard/createUiWppV2NonThreeD.php';

    /**
     * Method switchFrame
     * @param $wpp2
     */
    public function switchFrame($wpp2 = true)
    {
        parent::switchFrame($wpp2);
    }

    /**
     * Method prepareClick
     */
    public function prepareClick()
    {
        parent::prepareClick();
    }
    /**
     * Method Method prepareDataForField
     * @param string $cardData
     * @param null $type
     */
    public function fillCreditCardFields($cardData, $type = null)
    {
        parent::fillCreditCardFields($cardData, self::TYPE);
    }
}
