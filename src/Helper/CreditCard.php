<?php

namespace Augwa\PaymentGateway\Helper;

/**
 * Class CreditCard
 * @package Augwa\PaymentGateway\Helper\CreditCard
 */
class CreditCard
{

    /** @var string */
    protected $description = '';

    /** @var string */
    protected $name = '';

    /** @var string */
    protected $address1 = '';

    /** @var string */
    protected $address2 = '';

    /** @var string */
    protected $city = '';

    /** @var string */
    protected $state = '';

    /** @var string */
    protected $zipCode = '';

    /** @var string */
    protected $country = '';

    /** @var string */
    protected $countryCode = '';

    /** @var string */
    protected $cardNumber = '';

    /** @var \DateTime */
    protected $cardExpiry = '';

    /** @var string */
    protected $cardCVV = '';

    /** @var string */
    protected $phoneNumber = '';

    /** @var string */
    protected $emailAddress = '';

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $address1
     *
     * @return $this
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
        return $this;
    }

    /**
     * @return string string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address2
     *
     * @return $this
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $zipCode
     *
     * @return $this
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $countryCode
     *
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $cardNumber
     *
     * @return $this
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = preg_replace('/[^0-9]/', '', $cardNumber);
        return $this;
    }

    /**
     * @return string
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * @param int $month
     * @param int $year
     *
     * @return $this
     */
    public function setCardExpiry($month, $year)
    {
        $this->cardExpiry = new \DateTime;
        $this->cardExpiry->setTimestamp(mktime($hour = 0, $minute = 0, $second = 0, $month+1, $day = 0, $year));
        return $this;
    }

    /**
     * @return string
     */
    public function getMaskedCardNumber() {
        return sprintf('%s%s', substr($this->getCardNumber(), 0, 4), substr($this->getCardNumber(), -4));
    }

    /**
     * @return \DateTime
     */
    public function getCardExpiry()
    {
        return $this->cardExpiry;
    }

    /**
     * @param string $cvv
     *
     * @return $this
     */
    public function setCardCVV($cvv)
    {
        $this->cardCVV = $cvv;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardCVV()
    {
        return $this->cardCVV;
    }

    /**
     * @param string $phoneNumber
     *
     * @return $this
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $emailAddress
     *
     * @return $this
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $luhnCheck = $this->luhnCheck($this->getCardNumber());
        $expiryCheck = $this->getCardExpiry()->getTimestamp() > time();
        return $luhnCheck && $expiryCheck;
    }

    /**
     * @param $number
     *
     * @return bool
     */
    private function luhnCheck($number)
    {
        settype($number, 'string');
        $number = preg_replace('/[^0-9]/', '', $number);
        if (strlen($number) === 0) {
            return false;
        }
        $sumTable = [
            [0,1,2,3,4,5,6,7,8,9],
            [0,2,4,6,8,1,3,5,7,9]
        ];
        $sum = 0;
        $flip = 0;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $sum += $sumTable[$flip++ & 0x1][$number[$i]];
        }
        return $sum % 10 === 0;
    }
}
