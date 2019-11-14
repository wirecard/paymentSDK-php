<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */


namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Browser;
use Wirecard\PaymentSdk\Entity\Card;
use Wirecard\PaymentSdk\Entity\SubMerchantInfo;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class CreditCardTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class CreditCardTransaction extends Transaction implements Reservable
{
    const NAME = 'creditcard';
    const TYPE_CHECK_ENROLLMENT = 'check-enrollment';
    const DESCRIPTOR_LENGTH = 64;
    const DESCRIPTOR_ALLOWED_CHAR_REGEX = "/[^a-zA-Z0-9]/u";

    /**
     * @var string
     */
    private $tokenId;

    /**
     * @var string
     */
    private $termUrl;

    /**
     * @var string
     */
    private $paRes;

    /**
     * @var CreditCardConfig
     */
    private $config;

    /**
     * @var boolean
     */
    private $threeD;

    /**
     * @var Card $card
     */
    private $card;

    /**
     * @var SubMerchantInfo
     */
    protected $subMerchantInfo;

    /**
     * @param Card $card
     * @return $this
     *
     * @since 2.1.1
     */
    public function setCard(Card $card)
    {
        $this->card = $card;
        return $this;
    }

    /**
     * @param CreditCardConfig $config
     * @return CreditCardTransaction
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param string $tokenId
     */
    public function setTokenId($tokenId)
    {
        $this->tokenId = $tokenId;
    }

    /**
     * @return string
     * @since 4.0.0
     */
    public function getTokenId()
    {
        return $this->tokenId;
    }

    /**
     * @return string
     */
    public function getTermUrl()
    {
        return $this->termUrl;
    }

    /**
     * @param string $termUrl
     * @return $this
     */
    public function setTermUrl($termUrl)
    {
        $this->termUrl = $termUrl;

        return $this;
    }

    /**
     * @param string $paRes
     * @return CreditCardTransaction
     */
    public function setPaRes($paRes)
    {
        $this->paRes = $paRes;

        return $this;
    }

    /**
     * @param bool $threeD
     * @return CreditCardTransaction
     */
    public function setThreeD($threeD)
    {
        $this->threeD = $threeD;
        return $this;
    }

    /**
     * @param SubMerchantInfo $subMerchantInfo
     */
    public function setSubMerchantInfo($subMerchantInfo)
    {
        $this->subMerchantInfo = $subMerchantInfo;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        if (isset($this->endpoint)) {
            return $this->endpoint;
        }

        return self::ENDPOINT_PAYMENTS;
    }

    /**
     * @return bool
     */
    public function getThreeD()
    {
        return $this->isThreeD();
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $this->validate();
        $result = [
            'merchant-account-id' => [
                'value' => $this->isThreeD()
                    ? $this->config->getThreeDMerchantAccountId()
                    : $this->config->getMerchantAccountId()
            ]
        ];

        if (null !== $this->tokenId) {
            $result['card-token'] = [
                'token-id' => $this->tokenId,
            ];
        }

        if (null !== $this->paRes) {
            $result['three-d'] = [
                'pares' => $this->paRes,
            ];
        }

        if (null !== $this->card) {
            $result['card'] = $this->card->mappedProperties();
        }

        if (null !== $this->subMerchantInfo) {
            $result['sub-merchant-info'] = $this->subMerchantInfo->mappedProperties();
        }

        if ($this->retrieveTransactionType() === Transaction::TYPE_CHECK_ENROLLMENT
            && !$this->browser instanceof Browser
        ) {
            $this->setBrowser(new Browser());
        }

        return $result;
    }

    /**
     * @throws UnsupportedOperationException|MandatoryFieldMissingException
     * @return string
     */
    protected function retrieveTransactionType()
    {
        if (null !== $this->paRes) {
            return $this->operation;
        }

        return parent::retrieveTransactionType();
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
                $transactionType = self::TYPE_REFERENCED_AUTHORIZATION;
                break;
            case self::TYPE_CHECK_ENROLLMENT:
                $transactionType = self::TYPE_AUTHORIZATION;
                break;
            default:
                if ($this->isThreeD()) {
                    $transactionType = self::TYPE_CHECK_ENROLLMENT;
                } else {
                    $transactionType = self::TYPE_AUTHORIZATION;
                }
        }

        return $transactionType;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
                $transactionType = self::TYPE_CAPTURE_AUTHORIZATION;
                break;
            case self::TYPE_PURCHASE:
                $transactionType = self::TYPE_REFERENCED_PURCHASE;
                break;
            case self::TYPE_CHECK_ENROLLMENT:
                $transactionType = self::TYPE_PURCHASE;
                break;
            default:
                if ($this->isThreeD()) {
                    $transactionType = self::TYPE_CHECK_ENROLLMENT;
                } else {
                    $transactionType = self::TYPE_PURCHASE;
                }
        }

        return $transactionType;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
            case self::TYPE_REFERENCED_AUTHORIZATION:
                return self::TYPE_VOID_AUTHORIZATION;
            case self::TYPE_REFUND_CAPTURE:
                return self::TYPE_VOID_REFUND_CAPTURE;
            case self::TYPE_REFUND_PURCHASE:
                return self::TYPE_VOID_REFUND_PURCHASE;
            case self::TYPE_CREDIT:
                return self::TYPE_VOID_CREDIT;
            case self::TYPE_PURCHASE:
            case self::TYPE_REFERENCED_PURCHASE:
                return self::TYPE_VOID_PURCHASE;
            case self::TYPE_CAPTURE_AUTHORIZATION:
                return self::TYPE_VOID_CAPTURE;
            default:
                throw new UnsupportedOperationException('The transaction can not be canceled.');
        }
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForRefund()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for refund set.');
        }

        switch ($this->parentTransactionType) {
            case self::TYPE_PURCHASE:
            case self::TYPE_REFERENCED_PURCHASE:
                return self::TYPE_REFUND_PURCHASE;
            case self::TYPE_CAPTURE_AUTHORIZATION:
                return self::TYPE_REFUND_CAPTURE;
            default:
                throw new UnsupportedOperationException('The transaction can not be refunded.');
        }
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForCredit()
    {
        return self::TYPE_CREDIT;
    }

    /**
     * @return string
     */
    public function retrieveOperationType()
    {
        return ($this->operation === Operation::RESERVE) ? self::TYPE_AUTHORIZATION : self::TYPE_PURCHASE;
    }

    /**
     *
     * @throws \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    protected function validate()
    {
        if ($this->paRes === null && $this->tokenId === null && $this->parentTransactionId === null) {
            throw new MandatoryFieldMissingException(
                'At least one of these two parameters has to be provided: token ID, parent transaction ID.'
            );
        }
    }

    /**
     * @return boolean
     */
    public function isFallback()
    {
        if (null === $this->amount) {
            return false;
        }

        if (null === $this->config->getSslMaxLimit($this->amount->getCurrency())
            && null !== $this->config->getThreeDMinLimit($this->amount->getCurrency())
            && $this->config->getThreeDMinLimit($this->amount->getCurrency()) < $this->amount->getValue()
        ) {
            return true;
        }

        if (null !== $this->config->getSslMaxLimit($this->amount->getCurrency())
            && null !== $this->config->getThreeDMinLimit($this->amount->getCurrency())
            && $this->config->getThreeDMinLimit($this->amount->getCurrency()) < $this->amount->getValue()
            && $this->amount->getValue() <= $this->config->getSslMaxLimit($this->amount->getCurrency())
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return boolean
     * @deprecated 4.0.0 use getIsThreeD
     */
    protected function isThreeD()
    {
        if (null !== $this->threeD) {
            return $this->threeD;
        }

        if (null === $this->amount) {
            return false;
        }

        if (null !== $this->config->getThreeDMinLimit($this->amount->getCurrency())
            && $this->config->getThreeDMinLimit($this->amount->getCurrency()) < $this->amount->getValue()
        ) {
            return true;
        }

        if (null !== $this->config->getSslMaxLimit($this->amount->getCurrency())
            && $this->config->getSslMaxLimit($this->amount->getCurrency()) < $this->amount->getValue()
        ) {
            return true;
        }

        return false;
    }
}
