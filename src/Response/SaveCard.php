<?php

namespace Augwa\PaymentGateway\Response;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class SaveCard
 * @package Augwa\PaymentGateway\Response
 */
class SaveCard extends Response
{

    /** @var Helper\CreditCard */
    protected $creditCard;

    /** @var string */
    protected $token;

    /** @var string */
    protected $billingProfile;

    /** @var \DateTime */
    protected $tokenExpiryDate;

    /** @var string */
    protected $name;

    /**
     * @param Helper\CreditCard $creditCard
     *
     * @return $this
     */
    public function setCreditCard(Helper\CreditCard $creditCard)
    {
        $this->creditCard = $creditCard;
        return $this;
    }

    /**
     * @return Helper\CreditCard
     * @throws Exception\MissingDataException
     */
    public function getCreditCard()
    {
        if ($this->creditCard === null)
        {
            throw new Exception\MissingDataException('Credit Card not set');
        }
        return $this->creditCard;
    }

    /**
     * @param \DateTime $tokenExpiryDate
     *
     * @return $this
     */
    public function setTokenExpiryDate(\DateTime $tokenExpiryDate)
    {
        $this->tokenExpiryDate = $tokenExpiryDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTokenExpiryDate()
    {
        return $this->tokenExpiryDate;
    }

    /**
     * @param $token
     *
     * @return SaveCard $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    protected function fetch()
    {
        $this->api->saveCard($this->getCreditCard());
    }

    protected function postResponseAction()
    {
        // do nothing
    }

}