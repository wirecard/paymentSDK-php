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
    const CRYPTOGRAM_VALUE = 'eyJzaWduYXR1cmUiOiJNRVlDSVFDSVM0QVZRV2ZXTEFBbW56TVZsMXUwckViZnFIM3g0UDhXUVd6K1VrL3dJZ0loQUkvc3oyOHBqVTFBbmluRnlDVGVienAzcXI3ZGs2bXN1WHlZM1JyOCtGbW8iLCJwcm90b2NvbFZlcnNpb24iOiJFQ3YxIiwic2lnbmVkTWVzc2FnZSI6IntcImVuY3J5cHRlZE1lc3NhZ2VcIjpcIjl2RFVMS1JUMUpSWXRTWlZBbkRNR3VCNzJEUFVlb3pVQkJ6NDVmZ2J0bkdrTStRYTFYTThEanI2Q3BhNGxiWDlhOFczWTVhWXppYkNBRWhUWUNuakNLVXV0clp3bHNPYmpNUXJ6a0dPTWhobENkcnU2alFzMnp3ODZCaTVvNkFISThycVg3RFMrcUpSZk9XRmJEM2dHQ1VZVjdBUURUenQveCtNOFkzUkpOeFdIYUdIVE5DNDQ1OHowZHgra2VLWWF6YkJXUWpldXE0bnIzbmdQUkVFa1lXeTN3TFFYb3JjYzZRTUZIa2xJazJ1YzExa2ttWllKRksyU2tGbDQ4SDZ1aGdUeG8ycEJrWkVlR2EyUjVRTjc0NDJ2VHZ4bGxJQW9NMkc3UkNFNmlxODNQSzlaaUNtWURoYXBHaTI0NnpPRG56Z0tiMlFvSXVWS2wrZFRydUowdzdVRkJpRTBFZ3lWVU1iY1JmcjFQaWJtNThHaFhFRU9JTWV6OE55S0s1dFBmdFkwMmRiV3ZpNVFtRisyK2diUFA1Z1h2eCtJRTN1ZVJobGJYOWFmUFo5M1JRVmxKUFkvcWtMYkVIaW1tSkFRVW9HU1lrZTRlSEdRSE9ib2tDaU5WQlg3dTRsVEIzUmNhY1FiNlJ3bGdcXHUwMDNkXFx1MDAzZFwiLFwiZXBoZW1lcmFsUHVibGljS2V5XCI6XCJCSnNaYWdTclowNFl4SmhMbTNhNkhST3dWdkJYRnFnc1NETlc4eEZqU2E1NithdGVHb0l6NHdYc3VFamh2VDllMWNkL2k5VXJqT0t4cEpXUTZzQ0czdGtcXHUwMDNkXCIsXCJ0YWdcIjpcIngxNkU5Y3U1UTZscUhlMENzL0FtZ3drMzcxeGZNZWNsZXAwak5jRWtIeFlcXHUwMDNkXCJ9In0=';

    /**
     * @return string
     */
    public function getEndpoint()
    {
        if (null !== $this->parentTransactionId && $this->operation !== Operation::RESERVE) {
            return self::ENDPOINT_PAYMENTS;
        }

        return self::ENDPOINT_PAYMENT_METHODS;
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
            'cryptogram-value' => self::CRYPTOGRAM_VALUE
        ];

        return $properties;
    }
}
