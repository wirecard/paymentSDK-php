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

namespace Wirecard\PaymentSdk\Entity;

/**
 * Class AccountHolder
 * @package Wirecard\PaymentSdk\Entity
 *
 * An immutable entity representing an account holder.
 */
class AccountHolder implements MappableEntity
{
    const SHIPPING = 'shipping_';

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var string;
     */
    private $email;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var \DateTime
     */
    private $dateOfBirth;

    /**
     * @var string
     */
    private $crmId;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var string
     */
    private $shippingMethod;

    /**
     * @var string
     */
    private $socialSecurityNumber;


    public function __construct($simpleXmlElement = null)
    {
        if ($simpleXmlElement) {
            $this->parseAccountHolder($simpleXmlElement);
        }
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @param Address $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @param string $crmId
     */
    public function setCrmId($crmId)
    {
        $this->crmId = $crmId;
    }

    /**
     * @param \DateTime $dateOfBirth
     * @return AccountHolder
     */
    public function setDateOfBirth(\DateTime $dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function setShippingMethod($shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;
    }

    /**
     * @param string $securityNumber
     */
    public function setSocialSecurityNumber($securityNumber)
    {
        $this->socialSecurityNumber = $securityNumber;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $result = array();

        if (null !== $this->lastName) {
            $result['last-name'] = $this->lastName;
        }

        if (null !== $this->firstName) {
            $result['first-name'] = $this->firstName;
        }

        if (null !== $this->email) {
            $result['email'] = $this->email;
        }

        if (null !== $this->dateOfBirth) {
            $result['date-of-birth'] = $this->dateOfBirth->format('d-m-Y');
        }

        if (null !== $this->phone) {
            $result['phone'] = $this->phone;
        }

        if (null !== $this->address) {
            $result['address'] = $this->address->mappedProperties();
        }

        if (null !== $this->crmId) {
            $result['merchant-crm-id'] = $this->crmId;
        }

        if (null !== $this->gender) {
            $result['gender'] = $this->gender;
        }

        if (null !== $this->socialSecurityNumber) {
            $result['social-security-number'] = $this->socialSecurityNumber;
        }

        if (null !== $this->shippingMethod) {
            $result['shipping-method'] = $this->shippingMethod;
        }

        return $result;
    }

    /**
     * @param string $type
     * @return array
     */
    public function mappedSeamlessProperties($type = '')
    {
        $result = array();

        if (self::SHIPPING == $type) {
            if (null !== $this->phone) {
                $result[$type . 'phone'] = $this->phone;
            }

            if (null !== $this->address) {
                $result = array_merge($result, $this->address->mappedSeamlessProperties($type));
            }

            return $result;
        }

        if (null !== $this->email) {
            $result['email'] = $this->email;
        }

        if (null !== $this->dateOfBirth) {
            $result['date_of_birth'] = $this->dateOfBirth->format('d-m-Y');
        }

        if (null !== $this->phone) {
            $result['phone'] = $this->phone;
        }

        if (null !== $this->address) {
            $result = array_merge($result, $this->address->mappedSeamlessProperties());
        }

        if (null !== $this->crmId) {
            $result['merchant_crm_id'] = $this->crmId;
        }

        if (null !== $this->gender) {
            $result['gender'] = $this->gender;
        }

        if (null !== $this->socialSecurityNumber) {
            $result['consumer_social_security_number'] = $this->socialSecurityNumber;
        }

        return $result;
    }

    /**
     * Get html table with the set data
     * @param array $options
     * @return string
     * @since 3.2.0
     */
    public function getAsHtml($options = [])
    {
        $defaults = [
            'table_id' => null,
            'table_class' => null,
            'translations' => [
                'title' => 'Account Holder'

            ]
        ];

        $options = array_merge($defaults, $options);
        $translations = $options['translations'];

        $html = "<table id='{$options['table_id']}' class='{$options['table_class']}'><tbody>";
        foreach ($this->getAllSetData() as $key => $value) {
            $html .= "<tr><td>" . $this->translate($key, $translations) . "</td><td>" . $value . "</td></tr>";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Get all set data
     * @return array
     * @since 3.2.0
     */
    private function getAllSetData()
    {
        $data = $this->mappedProperties();
        if (isset($data['address'])) {
            $address = $data['address'];
            unset(
                $data['address']
            );

            return array_merge($data, $address);
        }

        return $data;
    }

    /**
     * Translate the table keys
     * @param $key
     * @param $translations
     * @return mixed
     * @since 3.2.0
     */
    private function translate($key, $translations)
    {
        if ($translations != null && isset($translations[$key])) {
            return $translations[$key];
        }

        return $key;
    }

    private function parseAccountHolder($simpleXmlElement)
    {
        $fields = [
            'first-name' => 'setFirstName',
            'last-name' => 'setLastName',
            'email' => 'setEmail',
            'phone' => 'setPhone'
        ];

        if (isset($simpleXmlElement->{'date-of-birth'})) {
            $dob = \DateTime::createFromFormat('d-m-Y', (string)$simpleXmlElement->{'date-of-birth'});
            if (!$dob) {
                $dob = \DateTime::createFromFormat('Y-m-d', (string)$simpleXmlElement->{'date-of-birth'});
            }
            $this->setDateOfBirth($dob);
        }

        foreach ($fields as $field => $function) {
            if (isset($simpleXmlElement->{$field})) {
                $this->{$function}((string)$simpleXmlElement->{$field});
            }
        }

        if (isset($simpleXmlElement->address)) {
            $address = new Address(
                (string)$simpleXmlElement->address->country,
                (string)$simpleXmlElement->address->city,
                (string)$simpleXmlElement->address->street1
            );

            if (isset($simpleXmlElement->address->{'postal-code'})) {
                $address->setPostalCode((string)$simpleXmlElement->address->{'postal-code'});
            }

            if (isset($simpleXmlElement->address->street2)) {
                $address->setStreet2((string)$simpleXmlElement->address->street2);
            }

            if (isset($simpleXmlElement->address->{'house-extension'})) {
                $address->setHouseExtension((string)$simpleXmlElement->address->{'house-extension'});
            }

            $this->setAddress($address);
        }

        return $this;
    }
}
