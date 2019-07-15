<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Entity\BankAccount;

class BankAccountUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BankAccount
     */
    private $bankAccount;

    private $bankName = 'A Bank Name';
    private $iban = 'IB01234567891';
    private $bic = 'BIC';

    public function setUp()
    {
        $this->bankAccount = new BankAccount();
    }

    public function testGetMappedPropertiesBankName()
    {
        $this->bankAccount->setBankName($this->bankName);

        $this->assertEquals(
            [
                'bank-name' => $this->bankName
            ],
            $this->bankAccount->mappedProperties()
        );
    }

    public function testGetMappedPropertiesIban()
    {
        $this->bankAccount->setIban($this->iban);

        $this->assertEquals(
            [
                'iban' => $this->iban
            ],
            $this->bankAccount->mappedProperties()
        );
    }

    public function testGetMappedPropertiesBic()
    {
        $this->bankAccount->setBic($this->bic);

        $this->assertEquals(
            [
                'bic' => $this->bic
            ],
            $this->bankAccount->mappedProperties()
        );
    }
}
