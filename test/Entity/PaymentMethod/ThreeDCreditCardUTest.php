<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Entity\PaymentMethod;

use Wirecard\PaymentSdk\Entity\PaymentMethod\ThreeDCreditCard;

class ThreeDCreditCardUTest extends \PHPUnit_Framework_TestCase
{
    const NOTIFICATION_TEST_URL = 'test URL';
    const TERM_TEST_URL = 'term test URL';
    /**
     * @var ThreeDCreditCard
     */
    private $transaction;


    const SAMPLE_TRANSACTION_ID = '542';

    public function setUp()
    {
        $this->transaction = new ThreeDCreditCard(
            self::SAMPLE_TRANSACTION_ID,
            self::NOTIFICATION_TEST_URL,
            self::TERM_TEST_URL
        );
    }

    public function testGetNotificationUrl()
    {
        $this->assertEquals(self::NOTIFICATION_TEST_URL, $this->transaction->getNotificationUrl());
    }

    public function testGetTermUrl()
    {
        $this->assertEquals(self::TERM_TEST_URL, $this->transaction->getTermUrl());
    }
}
