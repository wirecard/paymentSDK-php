<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
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
