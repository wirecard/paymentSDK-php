<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

class SofortTransaction extends Transaction
{
    const NAME = 'sofortbanking';

    /**
     * Maximum characters: 27
     */
    const DESCRIPTOR_LENGTH = 27;
    /**
     * Allowed characters:
     * umlaut, '0-9' 'a-z' 'A-Z' ' ' '+' ',' '-' '.'
     */
    const DESCRIPTOR_ALLOWED_CHAR_REGEX = "/[^a-zA-Z0-9\s\+\,\-\.\Ä\Ö\Ü\ä\ö\ü]/u";

    /**
     * @var bool
     */
    protected $sepaCredit = true;

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        return [
            'descriptor' => $this->descriptor
        ];
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        return self::TYPE_DEBIT;
    }
}
