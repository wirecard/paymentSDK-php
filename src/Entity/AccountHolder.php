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
 * Class AccountHolder
 * @package Wirecard\PaymentSdk\Entity
 *
 * An immutable entity representing an account holder.
 */
class AccountHolder implements MappableEntity
{
    const SHIPPING = 'shipping_';
    const DEF_FORMAT = 'd-m-Y';

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
     * @var string
     */
    private $mobilePhone;

    /**
     * @var string
     */
    private $workPhone;

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

    /**
     * @var AccountInfo
     */
    private $accountInfo;


    public function __construct($simpleXmlElement = null)
    {
        if ($simpleXmlElement) {
            $this->parseAccountHolder($simpleXmlElement);
        }
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @param mixed $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @param $phone
     * @return $this
     * @since 3.8.0
     */
    public function setMobilePhone($phone)
    {
        $this->mobilePhone = $phone;
        return $this;
    }

    /**
     * @param $phone
     * @return $this
     * @since 3.8.0
     */
    public function setWorkPhone($phone)
    {
        $this->workPhone = $phone;
        return $this;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @param string $crmId
     * @return $this
     */
    public function setCrmId($crmId)
    {
        $this->crmId = $crmId;
        return $this;
    }

    /**
     * @param \DateTime $dateOfBirth
     * @return AccountHolder
     * @return $this
     */
    public function setDateOfBirth(\DateTime $dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    /**
     * @param string $gender
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @param $shippingMethod
     * @return $this
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /**
     * @param string $securityNumber
     * @return $this
     */
    public function setSocialSecurityNumber($securityNumber)
    {
        $this->socialSecurityNumber = $securityNumber;
        return $this;
    }

    /**
     * @param string $format
     * @return string
     * @since 3.4.0
     */
    public function getDateOfBirth($format = self::DEF_FORMAT)
    {
        return $this->dateOfBirth->format($format);
    }

    /**
     * @param AccountInfo $accountInfo
     * @return $this
     * @since 3.8.0
     */
    public function setAccountInfo($accountInfo)
    {
        if (!$accountInfo instanceof AccountInfo) {
            throw new \InvalidArgumentException(
                '3DS Requestor Authentication Information must be of type AccountInfo.'
            );
        }
        $this->accountInfo = $accountInfo;

        return $this;
    }

    /**
     * @return AccountInfo
     * @since 3.8.0
     */
    public function getAccountInfo()
    {
        return $this->accountInfo;
    }

    /**
     * @return array
     */
    public function mappedProperties()
    {
        $result = array();

        if (!is_null($this->lastName)) {
            $result['last-name'] = $this->lastName;
        }

        if (!is_null($this->firstName)) {
            $result['first-name'] = $this->firstName;
        }

        if (!is_null($this->email)) {
            $result['email'] = $this->email;
        }

        if (!is_null($this->dateOfBirth)) {
            $result['date-of-birth'] = $this->dateOfBirth->format('d-m-Y');
        }

        if (!is_null($this->phone)) {
            $result['phone'] = $this->phone;
        }

        if (!is_null($this->mobilePhone)) {
            $result['mobile-phone'] = $this->mobilePhone;
        }

        if (!is_null($this->workPhone)) {
            $result['work-phone'] = $this->workPhone;
        }

        if (!is_null($this->address)) {
            $result['address'] = $this->address->mappedProperties();
        }

        if (!is_null($this->crmId)) {
            $result['merchant-crm-id'] = $this->crmId;
        }

        if (!is_null($this->gender)) {
            $result['gender'] = $this->gender;
        }

        if (!is_null($this->socialSecurityNumber)) {
            $result['social-security-number'] = $this->socialSecurityNumber;
        }

        if (!is_null($this->shippingMethod)) {
            $result['shipping-method'] = $this->shippingMethod;
        }

        if (!is_null($this->accountInfo)) {
            $result['account-info'] = $this->accountInfo->mappedProperties();
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
            if (!is_null($this->phone)) {
                $result[$type . 'phone'] = $this->phone;
            }

            if (!is_null($this->address)) {
                $result = array_merge($result, $this->address->mappedSeamlessProperties($type));
            }

            return $result;
        }

        if (!is_null($this->email)) {
            $result['email'] = $this->email;
        }

        if (!is_null($this->dateOfBirth)) {
            $result['date_of_birth'] = $this->dateOfBirth->format('d-m-Y');
        }

        if (!is_null($this->phone)) {
            $result['phone'] = $this->phone;
        }

        if (!is_null($this->mobilePhone)) {
            $result['mobile_phone'] = $this->mobilePhone;
        }

        if (!is_null($this->workPhone)) {
            $result['work_phone'] = $this->workPhone;
        }

        if (!is_null($this->address)) {
            $result = array_merge($result, $this->address->mappedSeamlessProperties());
        }

        if (!is_null($this->crmId)) {
            $result['merchant_crm_id'] = $this->crmId;
        }

        if (!is_null($this->gender)) {
            $result['gender'] = $this->gender;
        }

        if (!is_null($this->socialSecurityNumber)) {
            $result['consumer_social_security_number'] = $this->socialSecurityNumber;
        }

        if (!is_null($this->accountInfo)) {
            $result = array_merge($result, $this->accountInfo->mappedSeamlessProperties());
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

            $data = array_merge($data, $address);
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
        if (!is_null($translations) && isset($translations[$key])) {
            return $translations[$key];
        }

        return $key;
    }

    /**
     * @param $simpleXmlElement
     * @return $this
     */
    private function parseAccountHolder($simpleXmlElement)
    {
        $fields = [
            'first-name'   => 'setFirstName',
            'last-name'    => 'setLastName',
            'email'        => 'setEmail',
            'phone'        => 'setPhone',
            'work-phone'   => 'setWorkPhone',
            'mobile-phone' => 'setMobilePhone',
        ];

        if (isset($simpleXmlElement->{'date-of-birth'})) {
            $dob = \DateTime::createFromFormat('d-m-Y', strval($simpleXmlElement->{'date-of-birth'}));
            if (!$dob) {
                $dob = \DateTime::createFromFormat('Y-m-d', strval($simpleXmlElement->{'date-of-birth'}));
            }
            $this->setDateOfBirth($dob);
        }

        foreach ($fields as $field => $function) {
            if (isset($simpleXmlElement->{$field})) {
                $this->{$function}(strval($simpleXmlElement->{$field}));
            }
        }

        if (isset($simpleXmlElement->address)) {
            $address = new Address(
                $simpleXmlElement->address->country,
                $simpleXmlElement->address->city,
                strval($simpleXmlElement->address->street1)
            );

            if (isset($simpleXmlElement->address->{'postal-code'})) {
                $address->setPostalCode(strval($simpleXmlElement->address->{'postal-code'}));
            }

            if (isset($simpleXmlElement->address->street2)) {
                $address->setStreet2(strval($simpleXmlElement->address->street2));
            }

            if (isset($simpleXmlElement->address->street3)) {
                $address->setStreet3(strval($simpleXmlElement->address->street3));
            }

            if (isset($simpleXmlElement->address->{'house-extension'})) {
                $address->setHouseExtension(strval($simpleXmlElement->address->{'house-extension'}));
            }

            $this->setAddress($address);
        }

        return $this;
    }
}
