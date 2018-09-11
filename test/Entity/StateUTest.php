<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\State;

class StateUTest extends \PHPUnit_Framework_TestCase
{
    public function testSetCountry()
    {
        $state = new State();
        $state->setCountry(State::UNITED_STATES);

        $this->assertEquals("US", $state->getCountry());
    }

    public function testSetName()
    {
        $state = new State();
        $state->setCountry(State::UNITED_STATES);
        $state->setName("Oregon");

        $this->assertEquals("Oregon", $state->getName());
    }

    public function testStateCodeConversion()
    {
        // Tests state name using only ASCII characters.
        $state = new State();
        $state->setCountry(State::UNITED_STATES);
        $state->setName("Oregon");

        $this->assertEquals("OR", $state->getCode());

        // Tests state name with accent marks.
        $state = new State();
        $state->setCountry(State::ARGENTINA);
        $state->setName("Córdoba");

        $this->assertEquals("CÓRDOBA", $state->getCode());

        // Tests state name without proper accent marks.
        $state = new State();
        $state->setCountry(State::ARGENTINA);
        $state->setName("Cordoba");

        $this->assertEquals("CÓRDOBA", $state->getCode());

        // Tests returning of null if state is not available in country.
        $state = new State();
        $state->setCountry(State::UNITED_STATES);
        $state->setName("Distrito Federal");

        $this->assertNull($state->getCode());
    }
}
