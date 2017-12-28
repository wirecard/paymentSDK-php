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
}
