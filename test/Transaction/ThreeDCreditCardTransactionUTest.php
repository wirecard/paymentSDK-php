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
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Transaction\ThreeDCreditCardTransaction;

class ThreeDCreditCardTransactionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThreeDCreditCardTransaction
     */
    private $instance;

    public function setUp()
    {
        $this->instance = new ThreeDCreditCardTransaction();
    }

    public function testSetTermUrl()
    {
        $this->instance->setTermUrl('test');
        $this->assertAttributeEquals('test', 'termUrl', $this->instance);
    }

    public function testGetTermUrl()
    {
        $this->instance->setTermUrl('test');
        $this->assertEquals('test', $this->instance->getTermUrl());
    }

    public function testSetPaRes()
    {
        $this->instance->setPaRes('paRes');
        $this->assertAttributeEquals('paRes', 'paRes', $this->instance);
    }

    public function testGetPaRes()
    {
        $this->instance->setPaRes('paRes');
        $this->assertEquals('paRes', $this->instance->getPaRes());
    }

    public function testMappedPropertiesDefault()
    {
        $valid = [
            'payment-methods' => [
                'payment-method' => [
                    [
                        'name' => 'creditcard'
                    ]
                ]
            ],
            'transaction-type' => 'check-enrollment'
        ];
        $this->assertEquals($valid, $this->instance->mappedProperties('testtype'));
    }

    public function testMappedPropertiesPares()
    {
        $this->instance->setPaRes('pasdsgf');

        $valid = [
            'payment-methods' => [
                'payment-method' => [
                    [
                        'name' => 'creditcard'
                    ]
                ]
            ],
            'transaction-type' => 'testtype',
            'three-d' => [
                'pares' => 'pasdsgf'
            ]
        ];
        $this->assertEquals($valid, $this->instance->mappedProperties('testtype'));
    }
}
