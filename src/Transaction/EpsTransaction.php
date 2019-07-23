<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\BankAccount;

/**
 * Class EpsTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class EpsTransaction extends Transaction
{
    const NAME='eps';

    /**
     * Maximum 140 characters
     */
    const DESCRIPTOR_LENGTH = 140;

    /**
     * Allowed characters:
     * a b c d e f g h i j k l m n o p q r s t u v w x y z A B C D E F G H I J K L M N O P Q R S T U V W X Y Z
     * 0 1 2 3 4 5 6 7 8 9 - € $ § % ! = # ~ ; + / ? : ( ) . , ' & > < " * { } [ ] @ \ _ ° ^ | Ä Ö Ü ä ö ü ß Space
     */
    const DESCRIPTOR_ALLOWED_CHAR_REGEX =
        "/[^a-zA-Z0-9\s\-\€\$\§\%\!\=\#\~\;\+\/\?\:\(\)\.\,\\'\&\>\<\"\*\{\}\[\]\@\\\_\°\^\|\Ä\Ö\Ü\ä\ö\ü\ß]/u";

    /**
     * @var string
     */
    private $bankData;

    /**
     * @param BankAccount $bankAccount
     * @return $this
     */
    public function setBankAccount(BankAccount $bankAccount)
    {

        $this->bankData = $bankAccount->mappedProperties();
        return $this;
    }

    /**
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $data = [];

        if (null !== $this->bankData) {
            foreach ($this->bankData as $key => $val) {
                $data['bank-account'][$key] = $val;
            }
        }

        return $data;
    }

    protected function retrieveTransactionTypeForPay()
    {
        return Transaction::TYPE_DEBIT;
    }
}
