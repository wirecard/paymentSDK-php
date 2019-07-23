<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;

/**
 * Class CustomFieldTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
abstract class CustomFieldTransaction extends Transaction
{
    const RAW_PREFIX = '';

    /**
     * Add a custom field without default prefix to the customfields
     *
     * If a custom field with $customFieldKey exists, it will be overridden.
     *
     * @param string $customFieldKey
     * @param string|null $customFieldValue
     */
    public function setRawCustomField($customFieldKey, $customFieldValue = null)
    {
        $customFields = $this->getCustomFields();
        if (empty($customFields)) {
            $customFields = new CustomFieldCollection();
        }

        $it = $customFields->getIterator();
        foreach ($it as $index => $existingField) {
            if ($existingField->getName() === $customFieldKey) {
                $it->offsetUnset($index);
            }
        }

        $customFields->add(new CustomField($customFieldKey, $customFieldValue, self::RAW_PREFIX));
        $this->setCustomFields($customFields);
    }
}
