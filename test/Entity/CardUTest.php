<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\Card;

class CardUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Card
     */
    private $card;

    protected function setUp()
    {
        $this->card = new Card();
    }

    public function testMappingOnlyRequiredFields()
    {
        $expectedResult = [
            'card-type' => 'card type',
            'expiration-month' => 'expiration month',
            'expiration-year' => 'expiration year'
        ];

        $this->card->setExpirationMonth('expiration month');
        $this->card->setExpirationYear('expiration year');
        $this->card->setType('card type');

        $this->assertEquals($expectedResult, $this->card->mappedProperties());
    }

    public function testSetMerchantTokenizationFlag()
    {
        $expectedResult = [
            'card-type' => 'card type',
            'expiration-month' => 'expiration month',
            'expiration-year' => 'expiration year',
            'merchant-tokenization-flag' => true
        ];

        $this->card->setExpirationMonth('expiration month');
        $this->card->setExpirationYear('expiration year');
        $this->card->setMerchantTokenizationFlag(true);
        $this->card->setType('card type');

        $this->assertEquals($expectedResult, $this->card->mappedProperties());
    }
}
