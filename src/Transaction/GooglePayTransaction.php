<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class GooglePayTransaction
 *
 * @package Wirecard\PaymentSdk\Transaction
 */
class GooglePayTransaction extends Transaction implements Reservable
{
    const NAME = 'google-pay';

    const PAYMENT_METHOD_NAME = 'creditcard';
    const CARD_TYPE = 'visa';
    const CRYPTOGRAM_TYPE = 'google-pay';

    /**
     * Recived from google pay cryptogram value
     *
     * @var string
     */
    private $cryptogramValue;

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
     * Set cryptogram value
     *
     * @param string $cryptogram
     * @throws \Exception
     */
    public function setCryptogramValue($cryptogram)
    {
        // Validate cryptogram
        $cryptogramObject = json_decode($cryptogram, true);

        if ($cryptogramObject === null) {
            throw new \InvalidArgumentException('cryptogram cannot be decoded with json_decode.');
        }

        $cryptogramObject['signedMessage'] = json_decode($cryptogramObject['signedMessage'], true);

        if ($cryptogramObject['signedMessage'] === null) {
            throw new \InvalidArgumentException('signedMessage in cryptogram cannot be decoded with json_decode.');
        }

        if (!isset($cryptogramObject['signedMessage']['encryptedMessage'])) {
            throw new MandatoryFieldMissingException('encryptedMessage does not exist.');
        }

        // Code to base64
        $this->cryptogramValue = base64_encode($cryptogram);
    }

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        return [];
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        return self::TYPE_CAPTURE_AUTHORIZATION;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        if ($this->parentTransactionType === self::TYPE_AUTHORIZATION) {
            return self::TYPE_CAPTURE_AUTHORIZATION;
        }

        return self::TYPE_PURCHASE;
    }

    /**
     * @return string
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     */
    protected function retrieveTransactionTypeForRefund()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        switch ($this->parentTransactionType) {
            case $this::TYPE_PURCHASE:
                return 'refund-purchase';
            case $this::TYPE_CAPTURE_AUTHORIZATION:
                return 'refund-capture';
            default:
                throw new UnsupportedOperationException('The transaction can not be refunded.');
        }
    }

    /**
     * @return string
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        if ($this->parentTransactionType === self::TYPE_CAPTURE_AUTHORIZATION) {
            return self::TYPE_VOID_AUTHORIZATION;
        }

        if ($this->parentTransactionType === self::TYPE_PURCHASE) {
            return self::TYPE_REFUND_PURCHASE;
        }

        throw new UnsupportedOperationException('The transaction can not be canceled.');
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $properties = parent::mappedProperties();

        $properties['payment-methods']['payment-method'] = [['name' => self::PAYMENT_METHOD_NAME]];
        $properties['card'] = ['card-type' => self::CARD_TYPE];
        $properties['cryptogram'] = [
            'cryptogram-type' => self::CRYPTOGRAM_TYPE,
            'cryptogram-value' => $this->cryptogramValue
        ];

        return $properties;
    }
}
