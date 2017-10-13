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

namespace WirecardTest\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\AlipayCrossborderTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class AlipayCrossborderTransactionUTest extends \PHPUnit_Framework_TestCase
{
    const SUCCESS_URL = 'http://www.example.com/success';
    const CANCEL_URL = 'http://www.example.com/cancel';

    /**
     * @var AlipayCrossborderTransaction
     */
    private $tx;

    public function setUp()
    {
        $redirect = new Redirect(self::SUCCESS_URL, self::CANCEL_URL);
        $accountHolder = new AccountHolder();
        $accountHolder->setFirstName("Firstname");
        $accountHolder->setLastName("Lastname");
        $this->tx = new AlipayCrossborderTransaction();
        $this->tx->setRedirect($redirect);
        $this->tx->setAmount(new Amount(45, 'EUR'));
        $this->tx->setAccountHolder($accountHolder);
    }

    public function testMappedProperties()
    {
        $expectedResult = [
            'transaction-type' => Transaction::TYPE_DEBIT,
            'requested-amount' => [
                'currency' => 'EUR',
                'value' => '45'
            ],
            'payment-methods' => [
                'payment-method' => [
                    0 => [
                        'name' => 'alipay-xborder'
                    ]
                ]
            ],
            'cancel-redirect-url' => self::CANCEL_URL,
            'success-redirect-url' => self::SUCCESS_URL,
            'locale' => 'de',
            'entry-mode' => 'ecommerce',
            'account-holder' => [
                'first-name' => 'Firstname',
                'last-name' => 'Lastname'
            ]
        ];

        $this->tx->setOperation(Operation::PAY);

        $result = $this->tx->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMappedSpecificPropertiesWithoutAccountHolderThrowsException()
    {
        $this->tx->setAccountHolder(null);
        $this->tx->setOperation(Operation::PAY);
        $this->tx->mappedProperties();
    }
}
