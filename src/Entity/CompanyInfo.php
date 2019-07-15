<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity;

/**
 * Class CompanyInfo
 * @package Wirecard\PaymentSdk\Entity
 */
class CompanyInfo
{
    /**
     * @var string
     */
    private $companyName;

    /**
     * @var string
     */
    private $companyUid;

    /**
     * @var string
     */
    private $companyTradeRegisterNumber;

    /**
     * @var string
     */
    private $companyRegisterKey;

    public function __construct($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @return mixed
     */
    public function getCompanyUid()
    {
        return $this->companyUid;
    }

    /**
     * @param mixed $companyUid
     */
    public function setCompanyUid($companyUid)
    {
        $this->companyUid = $companyUid;
    }

    /**
     * @return mixed
     */
    public function getCompanyTradeRegisterNumber()
    {
        return $this->companyTradeRegisterNumber;
    }

    /**
     * @param mixed $companyTradeRegisterNumber
     */
    public function setCompanyTradeRegisterNumber($companyTradeRegisterNumber)
    {
        $this->companyTradeRegisterNumber = $companyTradeRegisterNumber;
    }

    /**
     * @return mixed
     */
    public function getCompanyRegisterKey()
    {
        return $this->companyRegisterKey;
    }

    /**
     * @param mixed $companyRegisterKey
     */
    public function setCompanyRegisterKey($companyRegisterKey)
    {
        $this->companyRegisterKey = $companyRegisterKey;
    }
}
