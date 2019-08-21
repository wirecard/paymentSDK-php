<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Page;

class SimulatorPage extends Base
{
    // include url of current page
    public $URL = 'https://test.wirecard.com.sg/mpisimulator/acsurl.jsp';

    //page specific text that can be found in the URL
    public $pageSpecific = 'simulator';

    //page elements
    public $elements = array(
        'Submit' => "//*[@type='submit']"
    );
}
