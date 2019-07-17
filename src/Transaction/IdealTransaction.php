<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\IdealBic;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;

/**
 * Class IdealTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class IdealTransaction extends Transaction
{
    const NAME = 'ideal';

    /**
     * Maximum characters: 35
     */
    const DESCRIPTOR_LENGTH = 35;

    /**
     * Allowed characters:
     * umlaut space 0-9 a-z A-Z ' + , - .
     */
    const DESCRIPTOR_ALLOWED_CHAR_REGEX = "/[^a-zA-Z0-9\s\'\+\,\-\.\Ä\Ö\Ü\ä\ö\ü]/u";

    /**
     * @var string
     */
    private $bic;

    /**
     * @var bool
     */
    protected $sepaCredit = true;

    /**
     * @param string $bank
     * @throws MandatoryFieldMissingException
     */
    public function setBic($bank)
    {
        $this->bic = IdealBic::search($bank);
        if (!$this->bic) {
            throw new MandatoryFieldMissingException('Bank does not participate in iDEAL or does not exist.');
        }
    }

    /**
     * @return array
     * @internal param null $requestId
     */
    protected function mappedSpecificProperties()
    {
        $join = (parse_url($this->redirect->getSuccessUrl(), PHP_URL_QUERY) ? '&' : '?');
        $successUrl = $this->redirect->getSuccessUrl() . $join . 'request_id=' . $this->requestId;
        $result['success-redirect-url'] = $successUrl;

        if (null !== $this->bic) {
            $result['bank-account']['bic'] = $this->bic;
        }
        if (null !== $this->descriptor) {
            $result['descriptor'] = $this->descriptor;
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        return self::TYPE_DEBIT;
    }
}
